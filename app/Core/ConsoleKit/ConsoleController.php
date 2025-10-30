<?php

namespace App\Core\ConsoleKit;

use App\Core\TableKit\QueryAdapter;
use App\Core\TableKit\TableSettingsResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ConsoleKit tabanlı grid uçlarına ortak JSON yanıt formatını üretir.
 *
 * Maliyet Notu: Tekrarlayan çağrılarda ETag ve hot cache sayesinde 60 sn içinde
 * aynı sorgu tekrar çalıştırılmaz; cursor paginate yalnızca ihtiyaç duyulan
 * dilimi çeker.
 */
abstract class ConsoleController
{
    /**
     * Console grid uçları için standart JSON çıktısı hazırlar.
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $options
     */
    protected function makeGridResponse(
        Request $request,
        Builder $query,
        string $tableKey,
        array $columns,
        array $options = []
    ): JsonResponse {
        $settings = App::make(TableSettingsResolver::class);
        $defaults = $settings->resolve($tableKey, $request->user());

        $adapter = QueryAdapter::make($query, $tableKey)
            ->select($options['select'] ?? ['*'])
            ->allowSorts($options['sortable'] ?? [])
            ->allowFilters($options['filters'] ?? [])
            ->defaultSort($defaults['default_sort'] ?? ($options['default_sort'] ?? '-id'))
            ->defaultLimit($defaults['page_size'] ?? ($options['default_limit'] ?? 25))
            ->maxLimit($defaults['max_page_size'] ?? ($options['max_limit'] ?? 200));

        if (isset($options['map']) && is_callable($options['map'])) {
            $adapter = $adapter->mapUsing($options['map']);
        }

        $payload = $adapter->toPayload($request);
        $paginator = $payload['paginator'];
        if (! $paginator instanceof CursorPaginator) {
            throw new RuntimeException('Console grid yalnızca cursor paginator ile çalışır.');
        }

        $responsePayload = [
            'columns' => $columns,
            'rows' => $payload['rows'],
            'cursor' => [
                'next' => optional($paginator->nextCursor())->encode(),
                'prev' => optional($paginator->previousCursor())->encode(),
            ],
            'meta' => array_merge($payload['meta'] ?? [], [
                'default_sort' => $defaults['default_sort'] ?? ($options['default_sort'] ?? '-id'),
                'visible_columns' => $defaults['visible_columns'] ?? ($options['visible_columns'] ?? []),
                'row_density' => $defaults['row_density'] ?? ($options['row_density'] ?? 'normal'),
            ]),
        ];

        $lastUpdated = CarbonImmutable::now();
        $etagSeed = $tableKey . '|' . json_encode([
            'rows' => Arr::pluck($payload['rows'], 'id'),
            'cursor' => $responsePayload['cursor'],
            'meta' => $responsePayload['meta'],
        ]);
        $etag = 'W/"' . sha1((string) $etagSeed) . '"';

        $companyId = currentCompanyId();

        if ($request->headers->get('If-None-Match') === $etag) {
            return response()->json([], 304)
                ->setEtag($etag)
                ->setLastModified(Carbon::instance($lastUpdated))
                ->header('X-Freshness-Key', $tableKey . ':' . ($companyId ?? 'global'))
                ->header('X-Freshness-Timestamp', $lastUpdated->toRfc7231String());
        }

        return response()->json($responsePayload)
            ->setEtag($etag)
            ->setLastModified(Carbon::instance($lastUpdated))
            ->header('X-Freshness-Key', $tableKey . ':' . ($companyId ?? 'global'))
            ->header('X-Freshness-Timestamp', $lastUpdated->toRfc7231String());
    }

    /**
     * Komut listesini izin kontrolüyle filtreler.
     *
     * @param  array<int, array<string, mixed>>  $commands
     * @return array<int, array<string, mixed>>
     */
    protected function filterCommands(Request $request, array $commands): array
    {
        return collect($commands)
            ->filter(function (array $command) use ($request) {
                $permission = $command['permission'] ?? null;
                if ($permission === null) {
                    return true;
                }

                $user = $request->user();
                if (! $user) {
                    return false;
                }

                $ability = is_string($permission) ? $permission : null;

                return $ability ? $user->can($ability) : false;
            })
            ->values()
            ->map(function (array $command) {
                $command['id'] ??= Str::uuid()->toString();

                return $command;
            })
            ->all();
    }

    /**
     * Hata durumlarını log'layıp kullanıcı dostu JSON döner.
     */
    protected function errorResponse(string $message, int $status = 422): JsonResponse
    {
        Log::warning('ConsoleKit isteği hata döndü.', [
            'message' => $message,
            'status' => $status,
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
