<?php

namespace App\Modules\Drive\Http\Controllers;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Core\Support\Models\Company;
use App\Http\Controllers\Controller;
use App\Modules\Drive\Domain\DriveStorage;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Http\Requests\ReplaceMediaRequest;
use App\Modules\Drive\Http\Requests\StoreManyMediaRequest;
use App\Modules\Drive\Http\Requests\StoreMediaRequest;
use App\Modules\Drive\Support\DriveStructure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use function currentCompanyId;
use function tenant;

class MediaController extends Controller
{
    public function __construct(private readonly DriveStorage $storage)
    {
        $this->middleware(function ($request, $next) {
            $this->authorize('viewAny', Media::class);

            return $next($request);
        })->only('index');

        $this->middleware(function ($request, $next) {
            $this->authorize('create', Media::class);

            return $next($request);
        })->only(['store', 'storeMany']);
    }

    public function index(Request $request): View
    {
        $pickerMode = $request->boolean('picker');
        $tab = $this->normalizeTab($request->query('tab'), $pickerMode);
        $companyId = $this->resolveCompanyId($request);
        $query = Media::query()
            ->with('uploader')
            ->where('company_id', $companyId);

        if ($pickerMode) {
            $pickerTab = array_key_first($this->pickerTabDefinitions());
            if ($pickerTab && str_contains($pickerTab, '__')) {
                [$moduleKey, $folderKey] = explode('__', Str::after($pickerTab, 'module_'), 2);
                $query->where('module', $moduleKey)->where('category', $folderKey);
            } else {
                $query->where('category', DriveStructure::normalizeFolderKey('products'));
            }
        }

        $query = $this->applyFilters($this->applyTabFilter($query, $tab), $request);

        $media = $query->paginate(12)->withQueryString();
        $stats = $pickerMode ? $this->buildPickerStats($companyId) : $this->buildStats($companyId);
        $tabs = $pickerMode ? $this->pickerTabDefinitions() : $this->tabDefinitions();
        $storage = $this->resolveStorageStats($companyId);

        return view('drive::index', [
            'tab' => $tab,
            'mediaItems' => $media,
            'stats' => $stats,
            'tabs' => $tabs,
            'pickerMode' => $pickerMode,
            'filters' => $request->only(['q', 'uploader', 'ext', 'mime', 'date_from', 'date_to', 'size_min', 'size_max', 'category', 'module']),
            'folderConfig' => DriveStructure::folders(),
            'moduleNavigation' => DriveStructure::navigation(),
            'globalMaxBytes' => (int) config('drive.max_upload_bytes', 50 * 1024 * 1024),
            'storage' => $storage,
        ]);
    }

    public function store(StoreMediaRequest $request)
    {
        $companyId = $this->resolveCompanyId($request);
        $data = $request->validated();
        $file = $request->file('file');
        $category = $data['category'];
        $module = $data['module'] ?? Media::MODULE_DEFAULT;

        $media = $this->persistUploadedFile($file, $category, $companyId, $module);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'media' => $this->serializeMedia($media),
                'message' => 'Dosya başarıyla yüklendi.',
            ], 201);
        }

        return redirect()
            ->route('admin.drive.media.index', ['tab' => $category])
            ->with('status', 'Dosya başarıyla yüklendi.');
    }

    public function storeMany(StoreManyMediaRequest $request): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        $data = $request->validated();
        $category = $data['category'];
        $module = $data['module'] ?? Media::MODULE_DEFAULT;
        $files = $request->file('files', []);

        $results = [
            'ok' => true,
            'uploaded' => [],
            'failed' => [],
        ];

        foreach ($files as $file) {
            try {
                $media = $this->persistUploadedFile($file, $category, $companyId, $module);
                $results['uploaded'][] = $this->serializeMedia($media);
            } catch (\Throwable $exception) {
                report($exception);
                $results['ok'] = false;
                $results['failed'][] = $file->getClientOriginalName();
            }
        }

        return response()->json($results);
    }

    public function replace(ReplaceMediaRequest $request, Media $media): JsonResponse
    {
        $this->authorize('replace', $media);

        $companyId = $this->resolveCompanyId($request);
        $file = $request->file('file');

        $this->ensureCompanyMatch($media, $companyId);

        $oldPaths = array_filter([$media->path, $media->thumb_path]);
        $meta = $this->uploadFile($file, $media->category, $companyId, $media->module, $media);

        $media->fill(array_merge($meta, [
            'visibility' => $meta['visibility'] ?? $media->visibility,
        ]));
        $media->uploaded_by = Auth::id();
        $media->save();

        $newPaths = array_filter([$media->path, $media->thumb_path]);
        $pathsToDelete = array_diff($oldPaths, $newPaths);

        if ($pathsToDelete) {
            try {
                $this->storage->filesystem($media->disk)->delete($pathsToDelete);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return response()->json([
            'ok' => true,
            'media' => $this->serializeMedia($media->fresh(['uploader'])),
        ]);
    }

    public function download(Media $media)
    {
        $this->authorize('download', $media);

        $filesystem = $this->storage->filesystem($media->disk);
        $url = $this->storage->temporaryUrl($media);

        if ($url) {
            return redirect()->away($url);
        }

        if (! $filesystem->exists($media->path)) {
            abort(404, 'Dosya bulunamadı.');
        }

        return $this->storage->download($media);
    }

    public function destroy(Request $request, Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        $force = $request->boolean('force');

        if ($force) {
            $media->forceDelete();
        } else {
            $media->delete();
        }

        $tab = $request->input('tab') ?: $request->query('tab');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'media_id' => $media->id,
            ]);
        }

        return redirect()
            ->route('admin.drive.media.index', array_filter(['tab' => $tab ?: $media->category]))
            ->with('status', 'Dosya silindi.');
    }

    public function toggleImportant(Media $media): JsonResponse
    {
        $this->authorize('markImportant', $media);

        $media->is_important = ! $media->is_important;
        $media->save();

        return response()->json([
            'ok' => true,
            'is_important' => $media->is_important,
        ]);
    }

    private function resolveStorageStats(int $companyId): array
    {
        $defaultLimit = (int) config('drive.default_storage_limit_bytes', 1_073_741_824);
        $company = tenant();

        if (! $company && $companyId) {
            $company = Company::query()->find($companyId);
        }

        $limit = (int) ($company?->drive_storage_limit_bytes ?? $defaultLimit);
        $used = (int) Media::query()->where('company_id', $companyId)->sum('size');
        $remaining = max($limit - $used, 0);
        $percentage = $limit > 0 ? round(min(100, ($used / $limit) * 100), 2) : 0.0;

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'percentage' => $percentage,
        ];
    }

    protected function applyTabFilter($query, string $tab)
    {
        $documentCategories = Media::documentCategories();
        $mediaCategories = Media::mediaCategories();

        if (str_starts_with($tab, 'module_')) {
            $rest = Str::after($tab, 'module_');

            if (str_contains($rest, '__')) {
                [$module, $folder] = explode('__', $rest, 2);

                if (in_array($module, Media::moduleKeys(), true) && DriveStructure::moduleAllowsFolder($module, $folder)) {
                    return $query->where('module', $module)->where('category', $folder)->orderByDesc('created_at');
                }
            }

            if (in_array($rest, Media::moduleKeys(), true)) {
                return $query->where('module', $rest)->orderByDesc('created_at');
            }
        }

        if (str_starts_with($tab, 'folder_')) {
            $folder = Str::after($tab, 'folder_');

            if (DriveStructure::folderExists($folder)) {
                return $query->where('category', $folder)->orderByDesc('created_at');
            }
        }

        return match ($tab) {
            'recent_documents' => $query->whereIn('category', $documentCategories)->orderByDesc('created_at'),
            'recent_media' => $query->whereIn('category', $mediaCategories)->orderByDesc('created_at'),
            'important_documents' => $query->whereIn('category', $documentCategories)->where('is_important', true)->orderByDesc('created_at'),
            'important_media' => $query->whereIn('category', $mediaCategories)->where('is_important', true)->orderByDesc('created_at'),
            'recent' => $query->where('created_at', '>=', now()->subDays(30))->orderByDesc('created_at'),
            'important' => $query->where('is_important', true)->orderByDesc('created_at'),
            default => $query->whereIn('category', $documentCategories)->orderByDesc('created_at'),
        };
    }

    protected function applyFilters($query, Request $request)
    {
        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = $request->input('q');
            $q->where(function ($inner) use ($term) {
                $inner->where('original_name', 'like', "%{$term}%")
                    ->orWhere('mime', 'like', "%{$term}%")
                    ->orWhere('ext', 'like', "%{$term}%");
            });
        });

        $query->when($request->filled('uploader'), function ($q) use ($request) {
            $q->where('uploaded_by', $request->input('uploader'));
        });

        $query->when($request->filled('ext'), function ($q) use ($request) {
            $q->where('ext', strtolower($request->input('ext')));
        });

        $query->when($request->filled('mime'), function ($q) use ($request) {
            $q->where('mime', strtolower($request->input('mime')));
        });

        $query->when($request->filled('category'), function ($q) use ($request) {
            $folder = DriveStructure::normalizeFolderKey($request->input('category'));
            $q->where('category', $folder);
        });

        $query->when($request->filled('module'), function ($q) use ($request) {
            $module = strtolower((string) $request->input('module'));
            if (in_array($module, Media::moduleKeys(), true)) {
                $q->where('module', $module);
            }
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            try {
                $date = Carbon::parse($request->input('date_from'))->startOfDay();
                $q->where('created_at', '>=', $date);
            } catch (\Throwable $exception) {
                // geçersiz tarih yok sayılır
            }
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            try {
                $date = Carbon::parse($request->input('date_to'))->endOfDay();
                $q->where('created_at', '<=', $date);
            } catch (\Throwable $exception) {
                // geçersiz tarih yok sayılır
            }
        });

        $query->when($request->filled('size_min'), function ($q) use ($request) {
            $min = (int) $request->input('size_min');
            $q->where('size', '>=', $min * 1024 * 1024);
        });

        $query->when($request->filled('size_max'), function ($q) use ($request) {
            $max = (int) $request->input('size_max');
            $q->where('size', '<=', $max * 1024 * 1024);
        });

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('dir', 'desc');

        $allowedSorts = ['created_at', 'size', 'original_name'];
        $allowedDirections = ['asc', 'desc'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, $allowedDirections, true)) {
            $direction = 'desc';
        }

        return $query->orderBy($sort, $direction);
    }

    protected function normalizeTab(?string $tab, bool $pickerMode = false): string
    {
        if ($pickerMode) {
            $pickerTabs = array_keys($this->pickerTabDefinitions());
            $defaultPicker = $pickerTabs[0] ?? 'recent_media';

            return in_array($tab, $pickerTabs, true) ? $tab : $defaultPicker;
        }

        $allowed = array_keys($this->tabDefinitions());
        if (in_array($tab, $allowed, true)) {
            return $tab;
        }

        $aliases = [
            'recent' => 'recent_documents',
            'important' => 'important_documents',
        ];

        if (isset($aliases[$tab])) {
            return $aliases[$tab];
        }

        return 'recent_documents';
    }

    protected function tabDefinitions(): array
    {
        $definitions = [
            'recent_documents' => 'Son Yüklenenler · Belgeler',
            'recent_media' => 'Son Yüklenenler · Medya',
            'important_documents' => 'Önemliler · Belgeler',
            'important_media' => 'Önemliler · Medya',
        ];

        foreach (DriveStructure::folders() as $folder) {
            $definitions['folder_' . $folder['key']] = $folder['label'];
        }

        foreach (DriveStructure::moduleOptions() as $module => $label) {
            $definitions['module_' . $module] = $label . ' · Tümü';

            foreach (DriveStructure::moduleFolderDefinitions($module) as $folder) {
                $definitions['module_' . $module . '__' . $folder['key']] = $label . ' · ' . $folder['label'];
            }
        }

        $definitions += [
            'recent' => 'Son Yüklenenler',
            'important' => 'Önemli',
        ];

        return $definitions;
    }

    protected function pickerTabDefinitions(): array
    {
        $module = Media::MODULE_INVENTORY;
        $folder = DriveStructure::normalizeFolderKey('products', $module);

        if (! DriveStructure::moduleAllowsFolder($module, $folder)) {
            $folder = DriveStructure::defaultFolder($module);
        }

        return [
            'module_' . $module . '__' . $folder => Media::moduleLabel($module) . ' · ' . DriveStructure::folderLabel($folder, $module),
        ];
    }

    protected function buildStats(int $companyId): array
    {
        /** @var InvalidationService $cache */
        $cache = app(InvalidationService::class);
        $key = Keys::forTenant($companyId, ['drive', 'stats'], 'v1');
        $ttl = (int) config('cache.ttl_profiles.warm', 900);

        return $cache->rememberWithTags(
            $key,
            [sprintf('tenant:%d', $companyId), 'drive', 'drive:stats'],
            $ttl,
            function () use ($companyId) {
                $base = Media::query()->where('company_id', $companyId);
                $documentCategories = Media::documentCategories();
                $mediaCategories = Media::mediaCategories();

                $stats = [
                    'recent_documents' => [
                        'total' => (clone $base)->whereIn('category', $documentCategories)->count(),
                        'important' => (clone $base)->whereIn('category', $documentCategories)->where('is_important', true)->count(),
                    ],
                    'recent_media' => [
                        'total' => (clone $base)->whereIn('category', $mediaCategories)->count(),
                        'important' => (clone $base)->whereIn('category', $mediaCategories)->where('is_important', true)->count(),
                    ],
                    'important_documents' => [
                        'total' => (clone $base)->whereIn('category', $documentCategories)->where('is_important', true)->count(),
                        'important' => (clone $base)->whereIn('category', $documentCategories)->where('is_important', true)->count(),
                    ],
                    'important_media' => [
                        'total' => (clone $base)->whereIn('category', $mediaCategories)->where('is_important', true)->count(),
                        'important' => (clone $base)->whereIn('category', $mediaCategories)->where('is_important', true)->count(),
                    ],
                ];

                foreach (DriveStructure::folders() as $folder) {
                    $stats['folder_' . $folder['key']] = [
                        'total' => (clone $base)->where('category', $folder['key'])->count(),
                        'important' => (clone $base)->where('category', $folder['key'])->where('is_important', true)->count(),
                    ];
                }

                foreach (Media::moduleKeys() as $module) {
                    $stats['module_' . $module] = [
                        'total' => (clone $base)->where('module', $module)->count(),
                        'important' => (clone $base)->where('module', $module)->where('is_important', true)->count(),
                    ];

                    foreach (DriveStructure::moduleFolderDefinitions($module) as $folder) {
                        $key = 'module_' . $module . '__' . $folder['key'];
                        $stats[$key] = [
                            'total' => (clone $base)
                                ->where('module', $module)
                                ->where('category', $folder['key'])
                                ->count(),
                            'important' => (clone $base)
                                ->where('module', $module)
                                ->where('category', $folder['key'])
                                ->where('is_important', true)
                                ->count(),
                        ];
                    }
                }

                return $stats;
            }
        );
    }

    protected function buildPickerStats(int $companyId): array
    {
        $definitions = $this->pickerTabDefinitions();
        $key = array_key_first($definitions);

        if (! $key) {
            return [];
        }

        $stats = $this->buildStats($companyId);

        return [
            $key => $stats[$key] ?? ['total' => 0, 'important' => 0],
        ];
    }

    protected function persistUploadedFile(UploadedFile $file, string $category, int $companyId, ?string $module = null): Media
    {
        $module = $module && in_array($module, Media::moduleKeys(), true)
            ? $module
            : DriveStructure::defaultModule();
        $category = DriveStructure::normalizeFolderKey($category, $module);
        $meta = $this->uploadFile($file, $category, $companyId, $module);

        return Media::create(array_merge($meta, [
            'company_id' => $companyId,
            'category' => $category,
            'module' => $module,
            'uploaded_by' => Auth::id(),
        ]));
    }

    protected function uploadFile(UploadedFile $file, string $category, int $companyId, string $module, ?Media $existing = null): array
    {
        $stored = $this->storage->put($file, $companyId, $module, $category, $existing?->visibility);

        return [
            'disk' => $stored->disk,
            'visibility' => $stored->visibility,
            'path' => $stored->path,
            'thumb_path' => null,
            'original_name' => $stored->originalName,
            'mime' => $stored->mime,
            'ext' => $stored->extension,
            'size' => $stored->size,
            'sha256' => $stored->hash,
            'width' => $stored->width,
            'height' => $stored->height,
        ];
    }

    protected function serializeMedia(?Media $media): array
    {
        $media = $media ?? null;

        if (! $media) {
            return [];
        }

        return [
            'id' => $media->id,
            'uuid' => $media->uuid,
            'category' => $media->category,
            'module' => $media->module,
            'module_label' => Media::moduleLabel($media->module),
            'original_name' => $media->original_name,
            'mime' => $media->mime,
            'ext' => $media->ext,
            'size' => $media->size,
            'size_human' => $media->humanSize(),
            'uploaded_at' => optional($media->created_at)->toDateTimeString(),
            'uploader' => $media->relationLoaded('uploader') ? optional($media->uploader)->only(['id', 'name', 'email']) : null,
            'is_important' => (bool) $media->is_important,
            'path' => $media->path,
            'thumb_path' => $media->thumb_path,
            'disk' => $media->disk,
            'visibility' => $media->visibility,
            'temporary_url' => $this->storage->temporaryUrl($media),
            'download_url' => route('admin.drive.media.download', $media),
        ];
    }

    protected function ensureCompanyMatch(Media $media, int $companyId): void
    {
        if ((int) $media->company_id !== $companyId) {
            abort(403, 'Bu dosyaya erişim yetkiniz yok.');
        }
    }

    protected function resolveCompanyId(Request $request): int
    {
        $attribute = $request->attributes->get('company_id');

        if ($attribute) {
            return (int) $attribute;
        }

        if ($tenant = tenant()) {
            return (int) $tenant->getKey();
        }

        return (int) (currentCompanyId() ?: 0);
    }
}
