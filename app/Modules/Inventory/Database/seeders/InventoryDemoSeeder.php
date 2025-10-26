<?php

namespace App\Modules\Inventory\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Support\DriveStructure;
use App\Modules\Inventory\Domain\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InventoryDemoSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::query()->get();

        if ($companies->isEmpty()) {
            return;
        }

        $productFolder = DriveStructure::normalizeFolderKey('products', Media::MODULE_INVENTORY);

        foreach ($companies as $company) {
            $mediaCollection = Media::query()
                ->where('company_id', $company->id)
                ->where('category', $productFolder)
                ->get();

            for ($index = 1; $index <= 11; $index++) {
                $sku = sprintf('DEMO-%s-%03d', strtoupper(Str::slug($company->name, '')), $index);
                $media = $mediaCollection->get(($index - 1) % max($mediaCollection->count(), 1));

                Product::query()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'sku' => $sku,
                    ],
                    [
                        'name' => $company->name . ' Ürün ' . $index,
                        'price' => round(49.9 + ($index * 5), 2),
                        'unit' => 'pcs',
                        'media_id' => $media?->id,
                        'description' => 'Bu kayıt, envanter modülünü tanıtmak için oluşturulmuş örnek bir üründür.',
                        'status' => $index % 4 === 0 ? 'inactive' : 'active',
                    ]
                );
            }
        }
    }
}
