<?php

namespace App\Cms\Support;

use App\Cms\Models\CmsContent;
use App\Cms\Support\PreviewStore;
use App\Modules\Inventory\Domain\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CmsRepository
{
    protected Application $app;

    protected CacheRepository $cache;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->cache = $app->make('cache.store');
    }

    public function read(string $page, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale();
        $cacheKey = $this->cacheKey($page, $locale);

        $data = $this->cache->remember($cacheKey, 3600, function () use ($page, $locale) {
            $model = CmsContent::query()
                ->forCompany($this->companyId())
                ->where('page', $page)
                ->where('locale', $locale)
                ->first();

            if ($model) {
                return $model->data ?? [];
            }

            if ($locale !== 'tr') {
                $fallback = CmsContent::query()
                    ->forCompany($this->companyId())
                    ->where('page', $page)
                    ->where('locale', 'tr')
                    ->first();

                if ($fallback) {
                    return $fallback->data ?? [];
                }
            }

            return $this->defaults($page);
        });

        if ($token = $this->previewStore()->token()) {
            $overlay = $this->previewStore()->get($token, $page, $locale);
            if (!empty($overlay)) {
                $data = $this->mergePreview($data, $overlay);
            }
        }

        return $data;
    }

    public function write(string $page, string $locale, array $data): void
    {
        CmsContent::query()->updateOrCreate(
            [
                'company_id' => $this->companyId(),
                'page' => $page,
                'locale' => $locale,
            ],
            [
                'data' => $data,
                'updated_by' => optional($this->app['auth']->user())->id,
            ]
        );

        $this->cache->forget($this->cacheKey($page, $locale));
    }

    public function get(string $path, mixed $default = null, ?string $locale = null): mixed
    {
        $segments = explode('.', $path);
        $page = array_shift($segments);
        $data = $this->read($page, $locale);

        return Arr::get($data['blocks'] ?? [], implode('.', $segments), $default);
    }

    public function locale(): string
    {
        return $this->app->getLocale() ?? 'tr';
    }

    public function companyId(): int
    {
        $request = $this->app['request'];
        if ($request && $request->attributes->has('company_id')) {
            return (int) $request->attributes->get('company_id');
        }

        if ($this->app->bound('tenant')) {
            $tenant = $this->app->make('tenant');
            if (is_object($tenant)) {
                if (isset($tenant->company_id)) {
                    return (int) $tenant->company_id;
                }
                if (method_exists($tenant, 'getKey')) {
                    return (int) $tenant->getKey();
                }
            }
        }

        if ($user = optional($this->app['auth'])->user()) {
            if (isset($user->company_id)) {
                return (int) $user->company_id;
            }
        }

        return (int) config('app.default_company_id', 0);
    }

    public function defaults(string $page): array
    {
        $blocks = [];
        $pageConfig = config('cms.pages.' . $page . '.blocks', []);
        foreach ($pageConfig as $blockKey => $definition) {
            if (!empty($definition['repeater'])) {
                $blocks[$blockKey] = [];
                continue;
            }

            $fields = [];
            foreach (($definition['fields'] ?? []) as $fieldKey => $meta) {
                $fields[$fieldKey] = null;
            }

            $blocks[$blockKey] = $fields;
        }

        return [
            'blocks' => $blocks,
            'seo' => [],
            'scripts' => [
                'header' => null,
                'footer' => null,
            ],
        ];
    }

    public function updateEmails(array $emails): void
    {
        $normalized = array_filter([
            'info_email' => $emails['info_email'] ?? null,
            'notify_email' => $emails['notify_email'] ?? null,
        ]);

        CmsContent::query()->updateOrCreate(
            [
                'company_id' => $this->companyId(),
                'page' => '_emails',
                'locale' => 'tr',
            ],
            [
                'data' => ['emails' => $normalized],
                'updated_by' => optional($this->app['auth']->user())->id,
            ]
        );

        $this->cache->forget($this->cacheKey('_emails', 'tr'));
    }

    public function emails(): array
    {
        if (function_exists('settings')) {
            $companyId = $this->companyId();
            $repo = settings();

            return array_filter([
                'info_email' => (string) $repo->get($companyId, 'email.outbound.x', ''),
                'notify_email' => (string) $repo->get($companyId, 'email.outbound.y', ''),
            ]);
        }

        $data = $this->read('_emails', 'tr');

        return $data['emails'] ?? config('cms.emails', []);
    }

    public function seo(string $page, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale();
        $data = $this->read($page, $locale);
        $seo = $data['seo'] ?? [];

        if (empty($seo) && $locale !== 'tr') {
            $seo = $this->read($page, 'tr')['seo'] ?? [];
        }

        return $seo;
    }

    public function scripts(string $page, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale();
        $data = $this->read($page, $locale);

        return $data['scripts'] ?? ['header' => null, 'footer' => null];
    }

    public function lastModifiedAt(string $page, string $locale): ?CarbonInterface
    {
        $record = CmsContent::query()
            ->forCompany($this->companyId())
            ->where('page', $page)
            ->where('locale', $locale)
            ->latest('updated_at')
            ->first();

        if ($record && $record->updated_at) {
            return $this->carbon($record->updated_at);
        }

        if ($locale !== 'tr') {
            return $this->lastModifiedAt($page, 'tr');
        }

        return null;
    }

    public function featuredProducts(int $limit = 6, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale();
        $query = $this->inventoryQuery();
        $model = method_exists($query, 'getModel') ? $query->getModel() : null;
        $table = $model && method_exists($model, 'getTable') ? $model->getTable() : 'products';

        if (Schema::hasColumn($table, 'is_featured')) {
            $query->where($table . '.is_featured', true);
        } elseif (Schema::hasColumn($table, 'featured')) {
            $query->where($table . '.featured', true);
        }

        return array_slice($this->transformProducts(
            $query->take($limit)->get()->all(),
            $locale
        ), 0, $limit);
    }

    public function allProducts(?string $locale = null): array
    {
        $locale = $locale ?? $this->locale();
        return $this->transformProducts($this->inventoryQuery()->get()->all(), $locale);
    }

    public function findProductBySlug(string $slug, ?string $locale = null): ?array
    {
        $locale = $locale ?? $this->locale();
        foreach ($this->allProducts($locale) as $product) {
            if (($product['slug'] ?? null) === $slug) {
                return $product;
            }
        }

        return null;
    }

    protected function cacheKey(string $page, string $locale): string
    {
        return sprintf('cms:%s:%s:%s', $this->companyId(), $locale, $page);
    }

    protected function inventoryQuery()
    {
        if (!class_exists(Product::class)) {
            return new class {
                public function where(...$args)
                {
                    return $this;
                }

                public function take($value)
                {
                    return $this;
                }

                public function get()
                {
                    return collect();
                }

                public function all()
                {
                    return [];
                }
            };
        }

        $query = Product::query()
            ->where('company_id', $this->companyId())
            ->with(['media', 'gallery.media']);

        $table = $query->getModel()->getTable();
        if (Schema::hasColumn($table, 'is_published')) {
            $query->where('is_published', true);
        }

        if (Schema::hasColumn($table, 'status')) {
            $query->where('status', 'active');
        }

        return $query;
    }

    protected function transformProducts(array $items, string $locale): array
    {
        $collection = [];
        foreach ($items as $product) {
            $name = $this->localizedAttribute($product, 'name', $locale) ?? '';
            $slug = $this->localizedAttribute($product, 'slug', $locale) ?: Str::slug($name);
            $updatedAt = $this->carbon($product->updated_at ?? null)
                ?? $this->carbon($product->created_at ?? null);
            $collection[] = [
                'id' => $product->id ?? null,
                'name' => $name,
                'slug' => $slug,
                'short_desc' => $this->localizedAttribute($product, 'short_desc', $locale) ?? '',
                'cover_image' => $this->mediaUrl($product->coverMedia ?? $product->media ?? null),
                'gallery' => $this->normaliseGallery($product->gallery ?? []),
                'sku' => $product->sku ?? null,
                'is_featured' => (bool) ($product->is_featured ?? false),
                'category' => $this->categorySlug($product, $locale),
                'updated_at' => $updatedAt,
            ];
        }

        return $collection;
    }

    protected function categorySlug(object $product, string $locale): string
    {
        $candidates = [
            $product->category_slug ?? null,
            $product->family_slug ?? null,
            $product->segment_slug ?? null,
            $this->localizedAttribute($product, 'category', $locale),
            $product->category ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && $value !== '') {
                return Str::of($value)->lower()->slug()->value();
            }
        }

        return 'all';
    }

    protected function normaliseGallery(mixed $gallery): array
    {
        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);

            return is_array($decoded) ? $decoded : [];
        }

        if ($gallery instanceof \Traversable) {
            $gallery = iterator_to_array($gallery);
        }

        if ($gallery instanceof \JsonSerializable) {
            $gallery = $gallery->jsonSerialize();
        }

        if (is_array($gallery)) {
            $urls = array_map(function ($item) {
                if (is_string($item)) {
                    return $item;
                }

                if (is_array($item)) {
                    return $item['url'] ?? $item['path'] ?? null;
                }

                if (is_object($item) && isset($item->media)) {
                    return $this->mediaUrl($item->media);
                }

                return null;
            }, $gallery);

            return array_values(array_filter($urls));
        }

        return [];
    }

    protected function localizedAttribute(object $model, string $attribute, string $locale): ?string
    {
        $localeAttribute = $attribute . '_' . $locale;
        $fallbackAttribute = $attribute . '_tr';

        if (isset($model->{$localeAttribute}) && filled($model->{$localeAttribute})) {
            return $model->{$localeAttribute};
        }

        if ($locale !== 'tr' && isset($model->{$fallbackAttribute}) && filled($model->{$fallbackAttribute})) {
            return $model->{$fallbackAttribute};
        }

        return $model->{$attribute} ?? null;
    }

    protected function mediaUrl(mixed $media): ?string
    {
        if (is_null($media)) {
            return null;
        }

        if (is_string($media)) {
            return $media;
        }

        if (is_array($media)) {
            $disk = $media['disk'] ?? config('filesystems.default');
            $path = $media['path'] ?? null;

            return $path ? Storage::disk($disk)->url($path) : null;
        }

        if (is_object($media)) {
            $disk = $media->disk ?? config('filesystems.default');
            $path = $media->path ?? null;

            if ($path) {
                try {
                    return Storage::disk($disk)->url($path);
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        return null;
    }

    protected function previewStore(): PreviewStore
    {
        return $this->app->make(PreviewStore::class);
    }

    protected function mergePreview(array $base, array $overlay): array
    {
        if (empty($base)) {
            return $overlay;
        }

        return array_replace_recursive($base, $overlay);
    }

    protected function carbon(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
