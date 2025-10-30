<?php

namespace App\Core\Reports;

use App\Core\Reports\Jobs\GenerateReportSnapshotJob;
use App\Core\Reports\Models\ReportSnapshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Cold rapor snapshot üretimi ve dosya kaydını yönetir.
 *
 * Maliyet Notu: Ağır sorgular queue içerisinde çalıştırılır; sonuç JSON olarak
 * saklanır ve 24 saate kadar cache'de tutulur. Dosya sistemi erişimleri tek seferde
 * yapılır, tekrar eden indirmelerde 304 yanıtı devreye girer.
 */
class ReportService
{
    public function __construct(private readonly ReportRegistry $registry)
    {
    }

    public function dispatch(int $companyId, string $reportKey, array $params = []): ReportSnapshot
    {
        $this->registry->find($reportKey);

        $snapshot = ReportSnapshot::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'report_key' => $reportKey,
                'params_hash' => $this->hashParams($params),
            ],
            [
                'status' => 'pending',
                'rows' => 0,
                'error' => null,
                'meta' => ['params' => $params],
                'storage_path' => null,
                'generated_at' => null,
                'valid_until' => null,
            ]
        );

        GenerateReportSnapshotJob::dispatch($snapshot->id);

        return $snapshot;
    }

    public function process(ReportSnapshot $snapshot): void
    {
        $definition = $this->registry->find($snapshot->report_key);
        $params = $snapshot->meta['params'] ?? [];

        $rows = $this->buildRows($snapshot->company_id, $snapshot->report_key, $params);
        $path = $this->storeSnapshot($snapshot->company_id, $snapshot->report_key, $rows);

        $ttl = (int) Arr::get($definition, 'ttl', 3600);

        $snapshot->update([
            'status' => 'ready',
            'rows' => count($rows),
            'generated_at' => now(),
            'valid_until' => now()->addSeconds($ttl),
            'storage_path' => $path,
            'meta' => array_merge($snapshot->meta ?? [], [
                'ttl' => $ttl,
                'params' => $params,
            ]),
        ]);

        $this->registry->markFresh($snapshot->company_id, $snapshot->report_key, $ttl);
        $this->registry->clearDirty($snapshot->company_id, $definition['depends_on'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function buildRows(int $companyId, string $reportKey, array $params): array
    {
        if ($reportKey === 'inventory.stock-summary') {
            return DB::table('stock_items as si')
                ->select([
                    'p.name as product_name',
                    'w.name as warehouse_name',
                    'si.qty',
                    'si.reserved_qty',
                    DB::raw('(si.qty - si.reserved_qty) as available_qty'),
                ])
                ->join('products as p', 'p.id', '=', 'si.product_id')
                ->join('warehouses as w', function ($join) use ($companyId): void {
                    $join->on('w.id', '=', 'si.warehouse_id')
                        ->where('w.company_id', '=', $companyId);
                })
                ->where('si.company_id', $companyId)
                ->orderBy('p.name')
                ->orderBy('w.name')
                ->get()
                ->map(fn ($row) => (array) $row)
                ->all();
        }

        throw new RuntimeException('Snapshot builder bulunamadı: ' . $reportKey);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function storeSnapshot(int $companyId, string $reportKey, array $rows): string
    {
        $disk = Storage::disk('local');
        $folder = "reports/{$companyId}/{$reportKey}";
        $file = $folder . '/' . Str::uuid() . '.json';

        $disk->put($file, json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $file;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function hashParams(array $params): string
    {
        return hash('sha1', json_encode($params));
    }
}
