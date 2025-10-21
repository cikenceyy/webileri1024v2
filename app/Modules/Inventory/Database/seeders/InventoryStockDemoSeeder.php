<?php

namespace App\Modules\Inventory\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

class InventoryStockDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! class_exists(StockService::class)) {
            return;
        }

        /** @var StockService $stockService */
        $stockService = App::make(StockService::class);

        $companies = Company::query()->get();

        foreach ($companies as $company) {
            $warehouses = Warehouse::query()->where('company_id', $company->id)->get();
            if ($warehouses->isEmpty()) {
                continue;
            }

            $defaultWarehouse = $warehouses->firstWhere('is_default', true) ?? $warehouses->first();
            $secondaryWarehouse = $warehouses->skip(1)->first();

            $products = Product::query()
                ->where('company_id', $company->id)
                ->orderBy('id')
                ->take(10)
                ->get();

            foreach ($products as $index => $product) {
                $hasMovements = StockMovement::query()
                    ->where('company_id', $company->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if ($hasMovements) {
                    continue;
                }

                $baseQty = 25 + ($index * 2);
                $unitCost = 40 + ($index * 5);

                try {
                    $stockService->receive($defaultWarehouse, $product, null, $baseQty, $unitCost, [
                        'reason' => 'opening',
                        'note' => 'Demo açılış stoğu',
                    ]);
                } catch (ValidationException $exception) {
                    continue;
                }

                $variant = ProductVariant::query()
                    ->where('company_id', $company->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($variant) {
                    try {
                        $stockService->receive($defaultWarehouse, $product, $variant, 5, $unitCost + 10, [
                            'reason' => 'opening',
                            'note' => 'Demo varyant açılış stoğu',
                        ]);
                    } catch (ValidationException) {
                        // ignore demo errors
                    }
                }

                if ($secondaryWarehouse) {
                    try {
                        $stockService->transfer($defaultWarehouse, $secondaryWarehouse, $product, null, 5, [
                            'note' => 'Demo transfer',
                        ]);
                    } catch (ValidationException) {
                    }
                }

                try {
                    $stockService->issue($defaultWarehouse, $product, null, 3, [
                        'reason' => 'sale',
                        'note' => 'Demo çıkış',
                    ]);
                } catch (ValidationException) {
                }

                try {
                    $stockService->adjust($defaultWarehouse, $product, null, -1.5, null, [
                        'note' => 'Demo sayım farkı',
                    ]);
                } catch (ValidationException) {
                }
            }
        }
    }
}
