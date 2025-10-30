<?php

namespace App\Core\TableKit\Services;

use App\Core\TableKit\Models\TablekitMetric;
use Carbon\CarbonImmutable;

/**
 * TableKit metrik kayıtlarını oluşturan servis sınıfıdır.
 * Amaç: Liste isteklerinden sonra tek satırlık ölçümleri hızlıca kaydetmek.
 */
class MetricRecorder
{
    /**
     * Ölçüm değerlerini kaydeder.
     */
    public function record(int $companyId, string $tableKey, array $data): void
    {
        $timestamp = CarbonImmutable::now();

        TablekitMetric::query()->create([
            'company_id' => $companyId,
            'table_key' => $tableKey,
            'ts' => $timestamp,
            'rows' => (int) ($data['rows'] ?? 0),
            'query_count' => (int) ($data['query_count'] ?? 0),
            'db_time_ms' => (int) ($data['db_time_ms'] ?? 0),
            'total_time_ms' => (int) ($data['total_time_ms'] ?? 0),
            'cache_hit' => (bool) ($data['cache_hit'] ?? false),
            'filters_hash' => (string) ($data['filters_hash'] ?? ''),
        ]);
    }
}
