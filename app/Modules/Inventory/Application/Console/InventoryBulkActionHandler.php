<?php

namespace App\Modules\Inventory\Application\Console;

use App\Core\Bulk\Models\BulkJob;
use App\Core\Reports\ReportRegistry;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Services\StockService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Inventory modülüne özgü bulk işlemleri yürütür.
 *
 * Maliyet Notu: Satır bazında işlemler chunk(25) ile yapılır ve her adımda
 * progress güncellenir; rapor snapshot'ları dirty olarak işaretlenir.
 */
class InventoryBulkActionHandler
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly ReportRegistry $reports,
    ) {
    }

    public function handle(BulkJob $job): void
    {
        $action = $job->action;
        $params = $job->params ?? [];
        $items = collect(Arr::get($params, 'items', []))
            ->filter(fn ($item) => isset($item['id']))
            ->values();

        if ($items->isEmpty()) {
            $job->update([
                'items_total' => 0,
                'items_done' => 0,
                'progress' => 100,
            ]);

            return;
        }

        $job->update(['items_total' => $items->count()]);

        $companyId = (int) $job->company_id;

        $items->chunk(25)->each(function ($chunk) use ($action, $job, $companyId): void {
            DB::transaction(function () use ($chunk, $action, $job, $companyId): void {
                foreach ($chunk as $item) {
                    $stockItem = StockItem::query()
                        ->where('company_id', $companyId)
                        ->find($item['id']);

                    if (! $stockItem) {
                        continue;
                    }

                    $qty = (float) ($item['qty'] ?? 0);

                    if ($action === 'reserve') {
                        $this->applyReserve($stockItem, $qty);
                    } elseif ($action === 'release') {
                        $this->applyRelease($stockItem, $qty);
                    } elseif ($action === 'adjust') {
                        $this->applyAdjust($stockItem, $qty, $item);
                    } else {
                        throw new RuntimeException('Tanımsız bulk işlemi: ' . $action);
                    }

                    $job->increment('items_done');
                }
            });

            $total = max(1, (int) $job->items_total);
            $done = (int) $job->items_done;
            $job->update(['progress' => min(95, (int) floor(($done / $total) * 90) + 5)]);
        });

        $this->reports->markDirty($companyId, ['inventory.stock']);
        $job->refresh();
    }

    private function applyReserve(StockItem $item, float $qty): void
    {
        $qty = max(0, $qty);
        $available = max(0, (float) $item->qty - (float) $item->reserved_qty);
        $item->reserved_qty = min((float) $item->reserved_qty + $qty, (float) $item->qty);
        if ($available <= 0) {
            Log::notice('Stokta rezerve edilecek miktar kalmadı.', [
                'stock_item_id' => $item->id,
            ]);
        }
        $item->save();
    }

    private function applyRelease(StockItem $item, float $qty): void
    {
        $qty = max(0, $qty);
        $item->reserved_qty = max(0, (float) $item->reserved_qty - $qty);
        $item->save();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function applyAdjust(StockItem $item, float $qty, array $context): void
    {
        $companyId = (int) $item->company_id;
        $warehouse = $item->warehouse;
        $product = $item->product;

        if (! $warehouse || ! $product) {
            return;
        }

        $this->stockService->adjust(
            $warehouse,
            $product,
            $item->variant,
            $qty,
            $context['unit_cost'] ?? null,
            [
                'reason' => 'console-bulk',
                'job_id' => $context['job_id'] ?? null,
            ]
        );

        $this->reports->markDirty($companyId, ['inventory.stock']);
    }
}
