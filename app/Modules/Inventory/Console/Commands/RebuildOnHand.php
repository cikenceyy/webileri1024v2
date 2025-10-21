<?php

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildOnHand extends Command
{
    protected $signature = 'stock:rebuild-onhand {--company=} {--dry-run}';

    protected $description = 'Recalculate stock item quantities from stock movements.';

    public function handle(): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'Running in dry-run mode.' : 'Rebuilding stock quantities…');

        $movements = StockMovement::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->selectRaw('company_id, warehouse_id, product_id, COALESCE(variant_id, 0) as variant_key, SUM(CASE WHEN direction = ? THEN qty ELSE -qty END) as on_hand', [StockMovement::DIRECTION_IN])
            ->groupBy('company_id', 'warehouse_id', 'product_id', 'variant_key')
            ->get();

        if ($dryRun) {
            $this->table(['Company', 'Warehouse', 'Product', 'Variant', 'Qty'], $movements->map(function ($row) {
                return [
                    $row->company_id,
                    $row->warehouse_id,
                    $row->product_id,
                    $row->variant_key ?: '—',
                    number_format((float) $row->on_hand, 3),
                ];
            })->toArray());

            return self::SUCCESS;
        }

        DB::transaction(function () use ($movements, $companyId): void {
            $query = StockItem::query()->when($companyId, fn ($q) => $q->where('company_id', $companyId));
            $query->update(['qty' => 0]);

            foreach ($movements as $row) {
                $attributes = [
                    'company_id' => $row->company_id,
                    'warehouse_id' => $row->warehouse_id,
                    'product_id' => $row->product_id,
                    'variant_id' => $row->variant_key ?: null,
                ];

                StockItem::query()->updateOrCreate($attributes, ['qty' => round((float) $row->on_hand, 3)]);
            }
        });

        $this->info('Stock quantities refreshed.');

        return self::SUCCESS;
    }
}
