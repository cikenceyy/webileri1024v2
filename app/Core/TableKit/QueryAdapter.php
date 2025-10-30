<?php

namespace App\Core\TableKit;

use App\Core\Cache\Keys;
use App\Core\TableKit\Services\MetricRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

/**
 * TableKit tabanlı liste ekranları için gelişmiş filtreleme, cursor paginate,
 * cache ve maliyet ölçümlerini yöneten yardımcı sınıf.
 *
 * Kullanım:
 * $adapter = QueryAdapter::make(Model::query(), 'workorders')
 *     ->select(['id', 'number'])
 *     ->allowSorts(['created_at', 'number'])
 *     ->allowFilters(['status' => ['type' => 'string']])
 *     ->defaultSort('-created_at');
 * return $adapter->toPayload($request);
 */
class QueryAdapter
{
    private array $select = ['*'];

    /** @var array<int, string> */
    private array $allowedSorts = [];

    /** @var array<string, array<string, mixed>> */
    private array $filterDefinitions = [];

    private string $defaultSort = '-id';

    private int $maxLimit = 50;

    private int $defaultLimit = 25;

    private int $hotTtl;

    private CacheRepository $cache;

    private ?string $cacheTag = null;

    /** @var callable|null */
    private $mapCallback = null;

    /** @var array<string, mixed> */
    private array $currentState = [];

    private MetricRecorder $metricRecorder;

    private int $dbTimeMs = 0;

    private int $queryCount = 0;

    private bool $lastCacheHit = false;

    private function __construct(
        private readonly Builder|BaseBuilder $query,
        private readonly string $cacheNamespace,
    ) {
        $this->cache = Cache::store();
        $this->hotTtl = (int) config('cache.ttl_profiles.hot', 60);
        $this->metricRecorder = App::make(MetricRecorder::class);
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
     * Dizide anahtar => değer formu ile tip/kolon belirtilir (örn. ['status' => ['type' => 'enum']]).
     */
    public function allowFilters(array $fields): self
    {
        $clone = clone $this;
        $clone->filterDefinitions = $clone->normalizeFilterDefinitions($fields);

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
        $state = $this->resolveState($request);
        $this->currentState = $state;

        $paginator = $this->buildPaginator($state);
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
                'state' => $state,
            ],
        ];
    }

    /**
     * Export işlemlerinde tekrar kullanılacak durumu verir.
     *
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return $this->currentState;
    }

    /**
     * Export job'larında kaydedilmiş state'i builder'a uygular.
     *
     * @param  array<string, mixed>  $state
     */
    public function applyStateToBuilder(Builder|BaseBuilder $builder, array $state): void
    {
        if ($this->select !== ['*']) {
            $builder->select($this->select);
        }

        $this->applyFiltersToBuilder($builder, $state['filters'] ?? []);
        $this->applySortsToBuilder($builder, $state['sort'] ?? [$this->defaultSort]);
    }

    /**
     * Request parametrelerinden filtre/sort/limit durumunu çözer.
     *
     * @return array<string, mixed>
     */
    private function resolveState(Request $request): array
    {
        $limit = $this->normalizeLimit($request->integer('limit', $this->defaultLimit));
        $sorts = $this->normalizeSorts($request->string('sort')->toString());
        $filters = $this->collectFilters($request);

        return [
            'limit' => $limit,
            'sort' => $sorts,
            'filters' => $filters,
            'cursor' => $request->query('cursor'),
            'filter_text' => $request->string('filter_text')->toString(),
        ];
    }

    /**
     * CLI veya export gibi işlemler için request'ten state'i hesaplayıp döner.
     *
     * @return array<string, mixed>
     */
    public function previewState(Request $request): array
    {
        return $this->resolveState($request);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function buildPaginator(array $state): CursorPaginator
    {
        $limit = (int) ($state['limit'] ?? $this->defaultLimit);
        $sorts = $state['sort'] ?? [$this->defaultSort];

        $builder = clone $this->query;

        if ($this->select !== ['*']) {
            $builder->select($this->select);
        }

        $this->applyFiltersToBuilder($builder, $state['filters'] ?? []);
        $this->applySortsToBuilder($builder, $sorts);

        $cursor = $state['cursor'] ?? null;
        $cacheKey = $this->resolveCacheKey($state, $limit);

        $connection = $builder->getConnection();
        $previousLogging = $connection->logging();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        $startedAt = microtime(true);

        $callback = function () use ($builder, $limit, $cursor): CursorPaginator {
            return $builder->cursorPaginate(
                perPage: $limit,
                columns: $this->select,
                cursorName: 'cursor',
                cursor: $cursor,
            )->withQueryString();
        };

        $paginator = $this->rememberWithTelemetry($cacheKey, $callback);

        $elapsed = (int) round((microtime(true) - $startedAt) * 1000);
        $queryLog = $connection->getQueryLog();
        $connection->disableQueryLog();

        if (! $previousLogging) {
            $connection->flushQueryLog();
        }

        $this->queryCount = count($queryLog);
        $this->dbTimeMs = (int) round(array_sum(array_column($queryLog, 'time')));

        $this->recordMetrics($paginator, $state, $elapsed);

        return $paginator;
    }

    private function normalizeLimit(int $limit): int
    {
        if ($limit < 1) {
            $limit = $this->defaultLimit;
        }

        return min($limit, $this->maxLimit);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeSorts(string $input): array
    {
        if ($input === '') {
            return [$this->defaultSort];
        }

        $requestedSorts = array_filter(array_map('trim', explode(',', $input)));
        if ($requestedSorts === []) {
            return [$this->defaultSort];
        }

        $normalized = [];
        foreach ($requestedSorts as $sort) {
            $field = ltrim($sort, '-');
            if ($this->allowedSorts !== [] && ! in_array($field, $this->allowedSorts, true)) {
                continue;
            }

            $normalized[] = Str::startsWith($sort, '-') ? '-' . $field : $field;
        }

        if ($normalized === []) {
            $normalized[] = $this->defaultSort;
        }

        return $normalized;
    }

    /**
     * @return array<int, array{field: string, operator: string, values: array<int, mixed>}>|
     */
    private function collectFilters(Request $request): array
    {
        $filters = [];

        foreach ($this->filterDefinitions as $field => $definition) {
            $value = $request->query($field);
            if ($value === null) {
                $bag = $request->query('filters');
                if (is_array($bag) && array_key_exists($field, $bag)) {
                    $value = $bag[$field];
                }
            }
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                if (isset($value['from']) || isset($value['to'])) {
                    $from = isset($value['from']) && $value['from'] !== ''
                        ? $this->castFilterValue($field, (string) $value['from'])
                        : null;
                    $to = isset($value['to']) && $value['to'] !== ''
                        ? $this->castFilterValue($field, (string) $value['to'])
                        : null;

                    if ($from !== null && $to !== null) {
                        $filters[] = [
                            'field' => $field,
                            'operator' => 'between',
                            'values' => [$from, $to],
                        ];
                        continue;
                    }

                    if ($from !== null) {
                        $filters[] = [
                            'field' => $field,
                            'operator' => 'gte',
                            'values' => [$from],
                        ];
                        continue;
                    }

                    if ($to !== null) {
                        $filters[] = [
                            'field' => $field,
                            'operator' => 'lte',
                            'values' => [$to],
                        ];
                        continue;
                    }

                    continue;
                }

                $values = array_map(fn ($item) => $this->castFilterValue($field, (string) $item), $value);
                $values = array_values(array_filter($values, static fn ($item) => $item !== null && $item !== ''));
                if ($values !== []) {
                    $filters[] = [
                        'field' => $field,
                        'operator' => 'in',
                        'values' => $values,
                    ];
                }
                continue;
            }

            $cast = $this->castFilterValue($field, (string) $value);
            if ($cast === null || $cast === '') {
                continue;
            }

            $filters[] = [
                'field' => $field,
                'operator' => 'eq',
                'values' => [$cast],
            ];
        }

        $advanced = $request->string('filter_text')->toString();
        if ($advanced !== '') {
            $filters = array_merge($filters, $this->parseAdvancedExpression($advanced));
        }

        return $this->enforceFilterLimits($filters);
    }

    /**
     * @param  array<int, array{field: string, operator: string, values: array<int, mixed>}>  $filters
     */
    private function applyFiltersToBuilder(Builder|BaseBuilder $builder, array $filters): void
    {
        foreach ($filters as $filter) {
            $field = $filter['field'];
            $definition = $this->filterDefinitions[$field] ?? null;
            if ($definition === null) {
                continue;
            }

            $column = $definition['column'] ?? $field;
            $values = $filter['values'];

            if ($filter['operator'] === 'between' && count($values) === 2) {
                [$from, $to] = $values;

                if ($from !== null && $to !== null) {
                    $builder->whereBetween($column, [$from, $to]);
                    continue;
                }

                if ($from !== null) {
                    $builder->where($column, '>=', $from);
                    continue;
                }

                if ($to !== null) {
                    $builder->where($column, '<=', $to);
                    continue;
                }

                continue;
            }

            if ($filter['operator'] === 'gte') {
                $builder->where($column, '>=', $values[0]);
                continue;
            }

            if ($filter['operator'] === 'lte') {
                $builder->where($column, '<=', $values[0]);
                continue;
            }

            if ($filter['operator'] === 'in') {
                if ($values !== []) {
                    $builder->whereIn($column, $values);
                }
                continue;
            }

            $builder->where($column, $values[0] ?? null);
        }
    }

    /**
     * @param  array<int, string>  $sorts
     */
    private function applySortsToBuilder(Builder|BaseBuilder $builder, array $sorts): void
    {
        foreach ($sorts as $sort) {
            $direction = Str::startsWith($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');

            if ($this->allowedSorts !== [] && ! in_array($field, $this->allowedSorts, true)) {
                $field = ltrim($this->defaultSort, '-');
            }

            $builder->orderBy($field, $direction);
        }
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function resolveCacheKey(array $state, int $limit): string
    {
        $companyId = (int) (function_exists('currentCompanyId') ? currentCompanyId() : 0);

        $parts = [
            'tablekit',
            $this->cacheNamespace,
            'list',
            md5(json_encode([
                'filters' => $state['filters'] ?? [],
                'sort' => $state['sort'] ?? [],
                'limit' => $limit,
                'cursor' => $state['cursor'] ?? null,
            ], JSON_THROW_ON_ERROR)),
        ];

        return Keys::forTenant($companyId, $parts);
    }

    /**
     * @param  array<int, mixed>  $fields
     * @return array<string, array<string, mixed>>
     */
    private function normalizeFilterDefinitions(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $normalized[$value] = ['type' => 'string'];
            } elseif (is_string($key) && is_array($value)) {
                $normalized[$key] = array_merge(['type' => 'string'], $value);
            } elseif (is_string($key) && is_string($value)) {
                $normalized[$key] = ['type' => $value];
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, array{field: string, operator: string, values: array<int, mixed>}>|
     */
    private function parseAdvancedExpression(string $expression): array
    {
        $matches = [];
        preg_match_all('/(?:^|\s)([\w.:-]+):(\"[^\"]*\"|[^\s]+)/u', $expression, $matches, PREG_SET_ORDER);

        $filters = [];

        foreach ($matches as $match) {
            $field = $match[1];
            if (! isset($this->filterDefinitions[$field])) {
                continue;
            }

            $raw = $match[2];
            if (Str::startsWith($raw, '"') && Str::endsWith($raw, '"')) {
                $raw = trim($raw, '"');
            }

            $operator = 'eq';
            $values = [];

            if (str_contains($raw, '..')) {
                $parts = array_map('trim', explode('..', $raw, 2));
                if (count($parts) === 2) {
                    $from = $parts[0] === '' ? null : $this->castFilterValue($field, $parts[0]);
                    $to = $parts[1] === '' ? null : $this->castFilterValue($field, $parts[1]);

                    if ($from !== null && $to !== null) {
                        $operator = 'between';
                        $values = [$from, $to];
                    } elseif ($from !== null) {
                        $operator = 'gte';
                        $values = [$from];
                    } elseif ($to !== null) {
                        $operator = 'lte';
                        $values = [$to];
                    }
                }
            } elseif (str_contains($raw, ',')) {
                $operator = 'in';
                $values = array_map(function (string $item) use ($field) {
                    return $this->castFilterValue($field, trim($item));
                }, array_filter(explode(',', $raw)));
            } else {
                $values = [$this->castFilterValue($field, $raw)];
            }

            $values = array_values(array_filter($values, static fn ($value) => $value !== null && $value !== ''));

            if ($values === []) {
                continue;
            }

            $filters[] = [
                'field' => $field,
                'operator' => $operator,
                'values' => $values,
            ];
        }

        return $filters;
    }

    private function castFilterValue(string $field, string $value): mixed
    {
        $definition = $this->filterDefinitions[$field] ?? ['type' => 'string'];
        $type = $definition['type'] ?? 'string';

        return match ($type) {
            'int', 'integer' => (int) $value,
            'float', 'number' => (float) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'date', 'datetime' => $this->castDate($value),
            default => $value,
        };
    }

    private function castDate(string $value): ?string
    {
        try {
            return CarbonImmutable::parse($value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, array{field: string, operator: string, values: array<int, mixed>}>  $filters
     * @return array<int, array{field: string, operator: string, values: array<int, mixed>}>
     */
    private function enforceFilterLimits(array $filters): array
    {
        $limited = [];

        foreach ($filters as $filter) {
            $values = $filter['values'];
            if ($filter['operator'] === 'in') {
                $values = array_slice($values, 0, 25);
            }

            if ($filter['operator'] === 'between' && count($values) === 2 && $this->isDateLike($filter['field'])) {
                try {
                    $start = CarbonImmutable::parse((string) $values[0]);
                    $end = CarbonImmutable::parse((string) $values[1]);
                    if ($start->diffInDays($end, false) > 90) {
                        $end = $start->addDays(90);
                        $values[1] = $end->format('Y-m-d');
                    }
                } catch (Throwable) {
                    // Geçersiz tarihleri görmezden gel.
                }
            }

            if (in_array($filter['operator'], ['gte', 'lte'], true) && $this->isDateLike($filter['field'])) {
                try {
                    $values[0] = CarbonImmutable::parse((string) $values[0])->format('Y-m-d');
                } catch (Throwable) {
                    continue;
                }
            }

            $filter['values'] = $values;
            $limited[] = $filter;
        }

        return $limited;
    }

    private function isDateLike(string $field): bool
    {
        $definition = $this->filterDefinitions[$field] ?? [];

        return in_array($definition['type'] ?? 'string', ['date', 'datetime'], true);
    }

    private function rememberWithTelemetry(string $cacheKey, callable $callback): CursorPaginator
    {
        if ($this->cacheTag !== null && method_exists($this->cache, 'tags')) {
            /** @var \Illuminate\Cache\TaggedCache $store */
            $store = $this->cache->tags([$this->cacheTag]);

            if ($store->has($cacheKey)) {
                $this->lastCacheHit = true;

                /** @var CursorPaginator $cached */
                $cached = $store->get($cacheKey);

                return $cached;
            }

            $this->lastCacheHit = false;
            $value = $callback();
            $store->put($cacheKey, $value, $this->hotTtl);

            return $value;
        }

        if ($this->cache->has($cacheKey)) {
            $this->lastCacheHit = true;

            /** @var CursorPaginator $cached */
            $cached = $this->cache->get($cacheKey);

            return $cached;
        }

        $this->lastCacheHit = false;
        $value = $callback();
        $this->cache->put($cacheKey, $value, $this->hotTtl);

        return $value;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function recordMetrics(CursorPaginator $paginator, array $state, int $elapsedMs): void
    {
        $companyId = (int) (function_exists('currentCompanyId') ? currentCompanyId() : 0);
        if ($companyId === 0) {
            return;
        }

        $filtersHash = sha1(json_encode($state['filters'] ?? [], JSON_THROW_ON_ERROR));

        $this->metricRecorder->record($companyId, $this->cacheNamespace, [
            'rows' => $paginator->count(),
            'query_count' => $this->queryCount,
            'db_time_ms' => $this->dbTimeMs,
            'total_time_ms' => $elapsedMs,
            'cache_hit' => $this->lastCacheHit,
            'filters_hash' => $filtersHash,
        ]);
    }
}
