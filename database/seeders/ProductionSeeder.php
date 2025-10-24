<?php

namespace Database\Seeders;

use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\BomItem;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Models\WorkOrderIssue;
use App\Modules\Production\Domain\Models\WorkOrderReceipt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBoms();
        $this->seedWorkOrders();
    }

    protected function seedBoms(): void
    {
        $definitions = [
            1 => [
                [
                    'code' => 'ACM-BOM-PMP',
                    'product_sku' => 'ACM-FG-01',
                    'output_qty' => 1,
                    'components' => [
                        ['sku' => 'ACM-RM-01', 'qty' => 2.5],
                        ['sku' => 'ACM-RM-02', 'qty' => 5],
                        ['sku' => 'ACM-PK-01', 'qty' => 1],
                    ],
                ],
                [
                    'code' => 'ACM-BOM-VLF',
                    'product_sku' => 'ACM-FG-02',
                    'output_qty' => 1,
                    'components' => [
                        ['sku' => 'ACM-RM-01', 'qty' => 1.2],
                        ['sku' => 'ACM-PK-02', 'qty' => 2],
                    ],
                ],
            ],
            2 => [
                [
                    'code' => 'BET-BOM-TSH',
                    'product_sku' => 'BET-FG-01',
                    'output_qty' => 10,
                    'components' => [
                        ['sku' => 'BET-RM-01', 'qty' => 12],
                        ['sku' => 'BET-RM-02', 'qty' => 120],
                    ],
                ],
                [
                    'code' => 'BET-BOM-SWT',
                    'product_sku' => 'BET-FG-03',
                    'output_qty' => 5,
                    'components' => [
                        ['sku' => 'BET-RM-01', 'qty' => 6],
                        ['sku' => 'BET-RM-02', 'qty' => 30],
                    ],
                ],
            ],
        ];

        foreach ($definitions as $companyId => $boms) {
            $warehouse = Warehouse::query()->where('company_id', $companyId)->first();

            foreach ($boms as $definition) {
                $product = Product::query()
                    ->where('company_id', $companyId)
                    ->where('sku', $definition['product_sku'])
                    ->first();

                if (! $product) {
                    continue;
                }

                $bom = Bom::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $definition['code'],
                    ],
                    [
                        'product_id' => $product->id,
                        'output_qty' => $definition['output_qty'],
                        'version' => 1,
                        'is_active' => true,
                    ]
                );

                $bom->items()->delete();
                $sort = 1;
                foreach ($definition['components'] as $component) {
                    $componentProduct = Product::query()
                        ->where('company_id', $companyId)
                        ->where('sku', $component['sku'])
                        ->first();

                    if (! $componentProduct) {
                        continue;
                    }

                    BomItem::query()->create([
                        'company_id' => $companyId,
                        'bom_id' => $bom->id,
                        'component_product_id' => $componentProduct->id,
                        'qty_per' => $component['qty'],
                        'wastage_pct' => 2.5,
                        'default_warehouse_id' => $warehouse?->id,
                        'sort' => $sort++,
                    ]);
                }
            }
        }
    }

    protected function seedWorkOrders(): void
    {
        $plans = [
            1 => [
                ['code' => 'ACM-WO-001', 'product_sku' => 'ACM-FG-01', 'bom_code' => 'ACM-BOM-PMP', 'qty' => 8, 'status' => 'in_progress'],
                ['code' => 'ACM-WO-002', 'product_sku' => 'ACM-FG-02', 'bom_code' => 'ACM-BOM-VLF', 'qty' => 15, 'status' => 'released'],
            ],
            2 => [
                ['code' => 'BET-WO-001', 'product_sku' => 'BET-FG-01', 'bom_code' => 'BET-BOM-TSH', 'qty' => 20, 'status' => 'in_progress'],
            ],
        ];

        $now = Carbon::now();

        foreach ($plans as $companyId => $workOrders) {
            $warehouse = Warehouse::query()->where('company_id', $companyId)->first();

            foreach ($workOrders as $plan) {
                $product = Product::query()
                    ->where('company_id', $companyId)
                    ->where('sku', $plan['product_sku'])
                    ->first();

                $bom = Bom::query()
                    ->where('company_id', $companyId)
                    ->where('code', $plan['bom_code'])
                    ->first();

                if (! $product || ! $bom) {
                    continue;
                }

                $workOrder = WorkOrder::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'doc_no' => $plan['code'],
                    ],
                    [
                        'product_id' => $product->id,
                        'bom_id' => $bom->id,
                        'target_qty' => $plan['qty'],
                        'uom' => 'adet',
                        'status' => $plan['status'],
                        'due_date' => $now->copy()->addDays(5),
                        'started_at' => $now->copy()->subDay(),
                        'notes' => 'Seed work order',
                    ]
                );

                if ($plan['status'] === 'in_progress') {
                    $workOrder->issues()->delete();
                    $workOrder->receipts()->delete();

                    $component = $bom->items()->first();
                    if ($component) {
                        WorkOrderIssue::query()->create([
                            'company_id' => $companyId,
                            'work_order_id' => $workOrder->id,
                            'component_product_id' => $component->component_product_id,
                            'warehouse_id' => $warehouse?->id,
                            'qty' => $component->qty_per * 4,
                            'posted_at' => $now->copy()->subHours(3),
                        ]);
                    }

                    WorkOrderReceipt::query()->create([
                        'company_id' => $companyId,
                        'work_order_id' => $workOrder->id,
                        'warehouse_id' => $warehouse?->id,
                        'qty' => $plan['qty'] / 2,
                        'posted_at' => $now->copy()->subHour(),
                    ]);
                }
            }
        }
    }
}
