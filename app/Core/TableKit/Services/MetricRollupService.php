<?php

namespace App\Core\TableKit\Services;

use App\Core\TableKit\Models\TablekitMetric;
use App\Core\TableKit\Models\TablekitMetricDaily;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * TableKit metriklerini günlük özet tablosuna taşıyan servis.
 * Amaç: Cron ile çağrılarak tenant bazlı performans raporlarını hazırlamak.
 */
class MetricRollupService
{
    /**
     * Verilen tarih için günlük özetleri hazırlar.
     */
    public function rollupForDate(CarbonImmutable $date): void
    {
        $start = $date->startOfDay();
        $end = $date->endOfDay();

        $metrics = TablekitMetric::query()
            ->whereBetween('ts', [$start, $end])
            ->orderBy('company_id')
            ->orderBy('table_key')
            ->get();

        if ($metrics->isEmpty()) {
            return;
        }

        $grouped = $metrics->groupBy(fn ($item) => $item->company_id . '|' . $item->table_key);

        DB::transaction(function () use ($grouped, $date): void {
            foreach ($grouped as $key => $items) {
                /** @var Collection<int, TablekitMetric> $items */
                [$companyId, $tableKey] = explode('|', $key, 2);
                $companyId = (int) $companyId;

                $requestCount = $items->count();
                $avgTotal = (int) round($items->avg('total_time_ms'));
                $avgDb = (int) round($items->avg('db_time_ms'));
                $avgQuery = (int) round($items->avg('query_count'));
                $cacheHitRatio = $requestCount > 0
                    ? round(($items->where('cache_hit', true)->count() / $requestCount) * 100, 2)
                    : 0.0;

                $p95 = $this->calculateP95($items->pluck('total_time_ms')->all());

                TablekitMetricDaily::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'table_key' => $tableKey,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'request_count' => $requestCount,
                        'avg_total_time_ms' => $avgTotal,
                        'p95_total_time_ms' => $p95,
                        'avg_query_count' => $avgQuery,
                        'avg_db_time_ms' => $avgDb,
                        'cache_hit_ratio' => $cacheHitRatio,
                    ],
                );
            }
        });
    }

    private function calculateP95(array $values): int
    {
        if ($values === []) {
            return 0;
        }

        sort($values, SORT_NUMERIC);
        $index = (int) ceil(0.95 * count($values)) - 1;
        $index = max(0, min($index, count($values) - 1));

        return (int) $values[$index];
    }
}
