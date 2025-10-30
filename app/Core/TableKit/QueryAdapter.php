<?php

namespace App\Core\TableKit;

use App\Core\Cache\Keys;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * TableKit tabanlı liste ekranları için cursor paginate + cache + whitelist
 * politikalarını tek yerde uygulayan yardımcı sınıf.
 *
 * Kullanım:
 * $adapter = QueryAdapter::make(Model::query(), 'workorders')
 *     ->select(['id', 'number'])
 *     ->allowSorts(['created_at', 'number'])
 *     ->allowFilters(['status'])
 *     ->defaultSort('-created_at');
 * return $adapter->toResponse($request);
 */
class QueryAdapter
{
    private array $select = ['*'];

    /** @var array<int, string> */
    private array $allowedSorts = [];

    /** @var array<int, string> */
    private array $allowedFilters = [];

    private string $defaultSort = '-id';

    private int $maxLimit = 50;

    private int $defaultLimit = 25;

    private int $hotTtl;

    private CacheRepository $cache;

    private ?string $cacheTag = null;

    /** @var callable|null */
    private $mapCallback = null;

    private function __construct(
        private readonly Builder|BaseBuilder $query,
        private readonly string $cacheNamespace,
    ) {
        $this->cache = Cache::store();
        $this->hotTtl = (int) config('cache.ttl_profiles.hot', 60);
    }

    /**
     * Factory helper.
     */
    public static function make(Builder|BaseBuilder $query, string $cacheNamespace): self
    {
        return new self($query, $cacheNamespace);
    }

    /**
     * Döndürülecek kolon listesini sınırlayın.
     */
    public function select(array $columns): self
    {
        $clone = clone $this;
        $clone->select = $columns;

        return $clone;
    }

    /**
     * İzin verilen sıralama alanlarını tanımlayın.
     */
    public function allowSorts(array $fields): self
    {
        $clone = clone $this;
        $clone->allowedSorts = $fields;

        return $clone;
    }

    /**
     * İzin verilen filtre alanlarını tanımlayın.
     */
    public function allowFilters(array $fields): self
    {
        $clone = clone $this;
        $clone->allowedFilters = $fields;

        return $clone;
    }

    /**
     * Varsayılan sıralama anahtarını (örn. "-created_at") belirleyin.
     */
    public function defaultSort(string $sort): self
    {
        $clone = clone $this;
        $clone->defaultSort = $sort;

        return $clone;
    }

    /**
     * Maksimum sayfa limitini ayarlayın.
     */
    public function maxLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->maxLimit = max(1, $limit);

        return $clone;
    }

    /**
     * Varsayılan limit değerini ayarlayın.
     */
    public function defaultLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->defaultLimit = max(1, $limit);

        return $clone;
    }

    /**
     * Cache tag kullanmak isterseniz (ör. settings, drive) burada set edin.
     */
    public function withCacheTag(string $tag): self
    {
        $clone = clone $this;
        $clone->cacheTag = $tag;

        return $clone;
    }

    /**
     * Row map işlemini (ör. formatlama) kapalı devre tanımlayın.
     */
    public function mapUsing(callable $callback): self
    {
        $clone = clone $this;
        $clone->mapCallback = $callback;

        return $clone;
    }

    /**
     * TableKit için gereken payload'ı üretir: rows + meta + paginator.
     */
    public function toPayload(Request $request): array
    {
        $paginator = $this->buildPaginator($request);
        $rows = array_map(function ($row) {
            if ($this->mapCallback !== null) {
                return ($this->mapCallback)($row);
            }

            if ($row instanceof Arrayable) {
                return $row->toArray();
            }

            return is_array($row) ? $row : (array) $row;
        }, $paginator->items());

        return [
            'rows' => $rows,
            'paginator' => $paginator,
            'meta' => [
                'default_sort' => $this->defaultSort,
                'limit' => $paginator->perPage(),
            ],
        ];
    }

    private function buildPaginator(Request $request): CursorPaginator
    {
        $limit = $this->resolveLimit($request);
        $sort = $this->resolveSort($request);

        $builder = clone $this->query;

        if ($this->select !== ['*']) {
            $builder->select($this->select);
        }

        $this->applyFilters($builder, $request);
        $this->applySort($builder, $sort);

        $cursor = $request->query('cursor');
        $cacheKey = $this->resolveCacheKey($request, $sort, $limit, $cursor);

        $callback = function () use ($builder, $limit, $cursor): CursorPaginator {
            return $builder->cursorPaginate(
                perPage: $limit,
                columns: $this->select,
                cursorName: 'cursor',
                cursor: $cursor,
            )->withQueryString();
        };

        if ($this->cacheTag !== null && method_exists($this->cache, 'tags')) {
            /** @var \Illuminate\Cache\TaggedCache $store */
            $store = $this->cache->tags([$this->cacheTag]);
            return $store->remember($cacheKey, $this->hotTtl, $callback);
        }

        return $this->cache->remember($cacheKey, $this->hotTtl, $callback);
    }

    private function resolveLimit(Request $request): int
    {
        $limit = $request->integer('limit', $this->defaultLimit);

        if ($limit < 1) {
            $limit = $this->defaultLimit;
        }

        return min($limit, $this->maxLimit);
    }

    private function resolveSort(Request $request): string
    {
        $requested = $request->string('sort')->toString();

        if ($requested === '') {
            return $this->defaultSort;
        }

        $field = ltrim($requested, '-');

        if ($this->allowedSorts !== [] && ! in_array($field, $this->allowedSorts, true)) {
            return $this->defaultSort;
        }

        return $requested;
    }

    private function applyFilters(Builder|BaseBuilder $builder, Request $request): void
    {
        if ($this->allowedFilters === []) {
            return;
        }

        foreach ($this->allowedFilters as $field) {
            $value = $request->query($field);
            if ($value === null || $value === '') {
                continue;
            }

            $builder->where($field, $value);
        }

        $search = $request->string('search')->toString();
        if ($search !== '' && method_exists($builder, 'scopeSearch')) {
            $builder->search($search);
        }
    }

    private function applySort(Builder|BaseBuilder $builder, string $sort): void
    {
        $direction = Str::startsWith($sort, '-') ? 'desc' : 'asc';
        $field = ltrim($sort, '-');

        if ($this->allowedSorts !== [] && ! in_array($field, $this->allowedSorts, true)) {
            $field = ltrim($this->defaultSort, '-');
        }

        $builder->orderBy($field, $direction);
    }

    private function resolveCacheKey(Request $request, string $sort, int $limit, ?string $cursor): string
    {
        $companyId = (int) (function_exists('currentCompanyId') ? currentCompanyId() : 0);

        $parts = [
            'tablekit',
            $this->cacheNamespace,
            'list',
            md5(json_encode([
                'query' => Arr::except($request->query(), ['cursor']),
                'sort' => $sort,
                'limit' => $limit,
                'cursor' => $cursor,
            ], JSON_THROW_ON_ERROR)),
        ];

        return Keys::forTenant($companyId, $parts);
    }
}
