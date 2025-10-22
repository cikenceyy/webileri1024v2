<?php

namespace App\Cms\Support;

use App\Cms\Models\CmsContent;
use App\Modules\Inventory\Domain\Models\Product;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
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

        return $this->cache->remember($cacheKey, 3600, function () use ($page, $locale) {
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

    public function featuredProducts(int $limit = 6, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale();
        return array_slice($this->transformProducts(
            $this->inventoryQuery()->where('is_featured', true)->take($limit)->get()->all(),
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

        $query = Product::query()->where('company_id', $this->companyId());

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
            $collection[] = [
                'id' => $product->id ?? null,
                'name' => $name,
                'slug' => $slug,
                'short_desc' => $this->localizedAttribute($product, 'short_desc', $locale) ?? '',
                'cover_image' => $product->cover_image ?? null,
                'gallery' => $product->gallery ?? [],
                'sku' => $product->sku ?? null,
                'is_featured' => (bool) ($product->is_featured ?? false),
            ];
        }

        return $collection;
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
}
