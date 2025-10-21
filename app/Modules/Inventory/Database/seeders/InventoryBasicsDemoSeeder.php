<?php

namespace App\Modules\Inventory\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\PriceListItem;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\ProductGallery;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\Unit;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class InventoryBasicsDemoSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company) {
            $this->seedUnits($company);
            $this->seedWarehouses($company);
            $categories = $this->seedCategories($company);
            $products = $this->seedProducts($company, $categories);
            $this->seedVariants($company, $products);
            $this->seedPriceLists($company, $products);
            $this->seedGalleries($company, $products);
        });
    }

    protected function seedUnits(Company $company): void
    {
        $definitions = [
            ['code' => 'pcs', 'name' => 'Adet', 'is_base' => true, 'to_base' => 1],
            ['code' => 'box', 'name' => 'Kutu', 'is_base' => false, 'to_base' => 10],
            ['code' => 'kg', 'name' => 'Kilogram', 'is_base' => false, 'to_base' => 1],
            ['code' => 'm', 'name' => 'Metre', 'is_base' => false, 'to_base' => 1],
        ];

        foreach ($definitions as $definition) {
            Unit::updateOrCreate(
                ['company_id' => 2, 'code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'is_base' => $definition['is_base'],
                    'to_base' => $definition['to_base'],
                ]
            );
        }
    }

    protected function seedWarehouses(Company $company): array
    {
        $records = [
            ['code' => 'MAIN', 'name' => 'Ana Ambar', 'is_default' => true],
            ['code' => 'SEC', 'name' => 'Yedek Ambar', 'is_default' => false],
        ];

        $warehouses = [];

        foreach ($records as $record) {
            $warehouse = Warehouse::updateOrCreate(
                ['company_id' => 2, 'code' => $record['code']],
                [
                    'name' => $record['name'],
                    'is_default' => $record['is_default'],
                    'status' => 'active',
                ]
            );

            $warehouses[] = $warehouse;
        }

        return $warehouses;
    }

    protected function seedCategories(Company $company): array
    {
        $names = ['Elektronik', 'Tekstil', 'Hırdavat', 'Ambalaj', 'Ofis'];
        $categories = [];

        foreach ($names as $index => $name) {
            $category = ProductCategory::updateOrCreate(
                ['company_id' => 2, 'code' => 'CAT-' . ($index + 1)],
                [
                    'name' => $name,
                    'status' => 'active',
                ]
            );

            $categories[] = $category;
        }

        return $categories;
    }

    protected function seedProducts(Company $company, array $categories): array
    {
        $defaultUnit = Unit::where('company_id', $company->id)->where('is_base', true)->first();
        $unitCode = $defaultUnit?->code ?? config('inventory.default_unit', 'pcs');
        $unitId = $defaultUnit?->id;

        $names = [
            'Akıllı Telefon', 'Bluetooth Kulaklık', 'LED Ampul', 'USB-C Kablo', 'Laptop Çantası',
            'Ofis Sandalyesi', 'Termal Yazıcı', 'Raf Etiketi', 'Metal Raf', 'Güvenlik Kamerası',
            'Pamuk T-Shirt', 'Polar Ceket', 'Çelik Çekiç', 'İzolasyon Bandı', 'Koli Bandı',
            'Karton Kutu', 'Plastik Kap', 'Dizüstü Bilgisayar', 'Router', 'Taşınabilir Şarj Cihazı',
        ];

        $products = [];

        foreach ($names as $index => $name) {
            $sku = sprintf('PRD-%d-%03d', $company->id, $index + 1);
            $category = $categories[$index % count($categories)] ?? null;

            $payload = [
                'name' => $name,
                'price' => rand(200, 2500),
                'description' => 'Demo ürün - ' . $name,
                'status' => 'active',
                'category_id' => $category?->id,
                'unit' => $unitCode,
                'reorder_point' => rand(5, 20),
            ];

            if ($unitId) {
                $payload['base_unit_id'] = $unitId;
            }

            $product = Product::updateOrCreate(
                ['company_id' => 2, 'sku' => $sku],
                $payload
            );

            $products[] = $product;
        }

        return $products;
    }

    protected function seedVariants(Company $company, array $products): void
    {
        $variantOptions = [
            ['Color' => 'Kırmızı', 'Size' => 'S'],
            ['Color' => 'Kırmızı', 'Size' => 'M'],
            ['Color' => 'Mavi', 'Size' => 'L'],
        ];

        foreach (array_slice($products, 0, 6) as $index => $product) {
            foreach ($variantOptions as $variantIndex => $options) {
                $sku = sprintf('%s-%02d', $product->sku, $variantIndex + 1);

                ProductVariant::updateOrCreate(
                    ['company_id' => 2, 'sku' => $sku],
                    [
                        'product_id' => $product->id,
                        'barcode' => Str::upper(Str::random(10)),
                        'options' => $options,
                        'status' => 'active',
                    ]
                );
            }
        }
    }

    protected function seedPriceLists(Company $company, array $products): void
    {
        $lists = [
            ['name' => 'Perakende', 'currency' => 'TRY', 'type' => 'sale', 'active' => true, 'multiplier' => 1.2],
            ['name' => 'Toptan', 'currency' => 'TRY', 'type' => 'sale', 'active' => true, 'multiplier' => 0.9],
        ];

        foreach ($lists as $list) {
            $priceList = PriceList::updateOrCreate(
                ['company_id' => 2, 'name' => $list['name']],
                [
                    'currency' => $list['currency'],
                    'type' => $list['type'],
                    'active' => $list['active'],
                ]
            );

            foreach ($products as $product) {
                $price = max(10, $product->price * $list['multiplier']);

                PriceListItem::updateOrCreate(
                    [
                        'company_id' => 2,
                        'price_list_id' => $priceList->id,
                        'product_id' => $product->id,
                        'variant_id' => null,
                    ],
                    ['price' => round($price, 2)]
                );
            }
        }
    }

    protected function seedGalleries(Company $company, array $products): void
    {
        $mediaItems = Media::query()
            ->where('company_id', $company->id)
            ->whereIn('category', [Media::CATEGORY_MEDIA_PRODUCTS, Media::CATEGORY_MEDIA_CATALOGS])
            ->get();

        foreach ($products as $product) {
            if ($mediaItems->isNotEmpty()) {
                $cover = $mediaItems->random();
                $product->update(['media_id' => $cover->id]);

                // Galeri: her durumda Collection üret
                $items = $mediaItems->count() > 1
                    ? $mediaItems->random(2)                 // 2 tane rastgele medya (Collection)
                    : collect([$mediaItems->random()]);      // tek medya → Collection’a sar

                $sort = 1;
                foreach ($items as $media) {                  // $media artık her zaman Model
                    ProductGallery::updateOrCreate(
                        [
                            'company_id' => 2,
                            'product_id' => $product->id,
                            'media_id'   => $media->id,
                        ],
                        ['sort_order' => $sort++]
                    );
                }
            }
        }
    }
}
