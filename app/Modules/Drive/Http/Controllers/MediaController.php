<?php

namespace App\Modules\Drive\Http\Controllers;

use App\Core\Support\Models\Company;
use App\Http\Controllers\Controller;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Http\Requests\ReplaceMediaRequest;
use App\Modules\Drive\Http\Requests\StoreManyMediaRequest;
use App\Modules\Drive\Http\Requests\StoreMediaRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function currentCompanyId;
use function tenant;

class MediaController extends Controller
{
    public function __construct()
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
        $query = Media::query()->with('uploader');

        if ($pickerMode) {
            $query->where('category', Media::CATEGORY_MEDIA_PRODUCTS);
        }

        $query = $this->applyFilters($this->applyTabFilter($query, $tab), $request);

        $media = $query->paginate(15)->withQueryString();
        $stats = $pickerMode ? $this->buildPickerStats() : $this->buildStats();
        $tabs = $pickerMode ? [Media::CATEGORY_MEDIA_PRODUCTS => 'Ürün Görselleri'] : $this->tabDefinitions();
        $storage = $this->resolveStorageStats();

        return view('drive::index', [
            'tab' => $tab,
            'mediaItems' => $media,
            'stats' => $stats,
            'tabs' => $tabs,
            'pickerMode' => $pickerMode,
            'filters' => $request->only(['q', 'uploader', 'ext', 'mime', 'date_from', 'date_to', 'size_min', 'size_max', 'category', 'module']),
            'categoryConfig' => config('drive.categories', []),
            'globalMaxBytes' => (int) config('drive.max_upload_bytes', 50 * 1024 * 1024),
            'storage' => $storage,
        ]);
    }

    public function store(StoreMediaRequest $request)
    {
        $companyId = (int) $request->attributes->get('company_id');
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
        $companyId = (int) $request->attributes->get('company_id');
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

        $companyId = (int) $request->attributes->get('company_id');
        $file = $request->file('file');

        $this->ensureCompanyMatch($media, $companyId);

        $oldPaths = array_filter([$media->path, $media->thumb_path]);
        $meta = $this->uploadFile($file, $media->category, $companyId, $media->disk);

        $media->fill($meta);
        $media->uploaded_by = Auth::id();
        $media->save();

        if ($oldPaths) {
            try {
                Storage::disk($media->disk)->delete($oldPaths);
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

        $disk = Storage::disk($media->disk);
        $ttl = (int) config('drive.presign_ttl_seconds', 300);

        try {
            $url = $disk->temporaryUrl($media->path, now()->addSeconds($ttl), [
                'ResponseContentDisposition' => 'attachment; filename="' . addslashes($media->original_name) . '"',
            ]);

            return redirect()->away($url);
        } catch (\Throwable $exception) {
            if (! $disk->exists($media->path)) {
                abort(404, 'Dosya bulunamadı.');
            }

            return $disk->download($media->path, $media->original_name, [
                'Content-Type' => $media->mime,
            ]);
        }
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

    private function resolveStorageStats(): array
    {
        $defaultLimit = (int) config('drive.default_storage_limit_bytes', 1_073_741_824);
        $company = tenant();

        if (! $company && ($companyId = currentCompanyId())) {
            $company = Company::query()->find($companyId);
        }

        $limit = (int) ($company->drive_storage_limit_bytes ?? $defaultLimit);
        $used = (int) Media::query()->sum('size');
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
            $module = Str::after($tab, 'module_');
            if (in_array($module, Media::moduleKeys(), true)) {
                return $query->where('module', $module)->orderByDesc('created_at');
            }
        }

        return match ($tab) {
            'recent_documents' => $query->whereIn('category', $documentCategories)->orderByDesc('created_at'),
            'recent_media' => $query->whereIn('category', $mediaCategories)->orderByDesc('created_at'),
            'important_documents' => $query->whereIn('category', $documentCategories)->where('is_important', true)->orderByDesc('created_at'),
            'important_media' => $query->whereIn('category', $mediaCategories)->where('is_important', true)->orderByDesc('created_at'),
            'recent' => $query->where('created_at', '>=', now()->subDays(30))->orderByDesc('created_at'),
            'important' => $query->where('is_important', true)->orderByDesc('created_at'),
            Media::CATEGORY_DOCUMENTS,
            Media::CATEGORY_MEDIA_PRODUCTS,
            Media::CATEGORY_MEDIA_CATALOGS,
            Media::CATEGORY_PAGES => $query->where('category', $tab)->orderByDesc('created_at'),
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
            return Media::CATEGORY_MEDIA_PRODUCTS;
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

        $definitions = array_merge($definitions, $this->moduleDefinitions());

        $definitions += [
            Media::CATEGORY_DOCUMENTS => 'Belgeler',
            Media::CATEGORY_MEDIA_PRODUCTS => 'Ürün Görselleri',
            Media::CATEGORY_MEDIA_CATALOGS => 'Katalog İçerikleri',
            Media::CATEGORY_PAGES => 'Sayfa Dosyaları',
            'recent' => 'Son Yüklenenler',
            'important' => 'Önemli',
        ];

        return $definitions;
    }

    protected function buildStats(): array
    {
        $base = Media::query();
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

        foreach ([
            Media::CATEGORY_DOCUMENTS,
            Media::CATEGORY_MEDIA_PRODUCTS,
            Media::CATEGORY_MEDIA_CATALOGS,
            Media::CATEGORY_PAGES,
        ] as $category) {
            $stats[$category] = [
                'total' => (clone $base)->where('category', $category)->count(),
                'important' => (clone $base)->where('category', $category)->where('is_important', true)->count(),
            ];
        }

        foreach (Media::moduleKeys() as $module) {
            $stats['module_' . $module] = [
                'total' => (clone $base)->where('module', $module)->count(),
                'important' => (clone $base)->where('module', $module)->where('is_important', true)->count(),
            ];
        }

        return $stats;
    }

    protected function moduleDefinitions(): array
    {
        return collect(Media::moduleOptions())
            ->mapWithKeys(static fn ($label, $slug) => ['module_' . $slug => $label])
            ->all();
    }

    protected function buildPickerStats(): array
    {
        return [
            Media::CATEGORY_MEDIA_PRODUCTS => [
                'total' => Media::query()->where('category', Media::CATEGORY_MEDIA_PRODUCTS)->count(),
                'important' => Media::query()->where('category', Media::CATEGORY_MEDIA_PRODUCTS)->where('is_important', true)->count(),
            ],
        ];
    }

    protected function persistUploadedFile(UploadedFile $file, string $category, int $companyId, ?string $module = null): Media
    {
        $meta = $this->uploadFile($file, $category, $companyId);

        return Media::create(array_merge($meta, [
            'company_id' => $companyId,
            'category' => $category,
            'module' => $module && in_array($module, Media::moduleKeys(), true)
                ? $module
                : Media::MODULE_DEFAULT,
            'uploaded_by' => Auth::id(),
        ]));
    }

    protected function uploadFile(UploadedFile $file, string $category, int $companyId, ?string $disk = null): array
    {
        $disk = $disk ?: config('drive.disk', config('filesystems.default', 'public'));
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $prefix = (string) config('drive.path_prefix', 'companies/{company_id}/drive');
        $basePath = trim(str_replace('{company_id}', (string) $companyId, $prefix), '/');
        $folder = trim($basePath . '/' . trim($category, '/') . '/' . now()->format('Y/m'), '/');
        $filename = Str::uuid() . '.' . $extension;

        $storedPath = $file->storeAs($folder, $filename, [
            'disk' => $disk,
            'visibility' => 'public',
        ]);

        $sha = null;
        try {
            $sha = hash_file('sha256', $file->getRealPath());
        } catch (\Throwable $exception) {
            report($exception);
        }

        $dimensions = null;
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif'], true)) {
            $dimensions = @getimagesize($file->getRealPath());
        }

        return [
            'disk' => $disk,
            'path' => $storedPath,
            'thumb_path' => null,
            'original_name' => $file->getClientOriginalName(),
            'mime' => strtolower((string) $file->getClientMimeType()),
            'ext' => $extension,
            'size' => $file->getSize(),
            'sha256' => $sha ?: null,
            'width' => is_array($dimensions) ? $dimensions[0] : null,
            'height' => is_array($dimensions) ? $dimensions[1] : null,
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
            'download_url' => route('admin.drive.media.download', $media),
        ];
    }

    protected function ensureCompanyMatch(Media $media, int $companyId): void
    {
        if ((int) $media->company_id !== $companyId) {
            abort(403, 'Bu dosyaya erişim yetkiniz yok.');
        }
    }
}
