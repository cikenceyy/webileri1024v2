<?php

namespace Database\Seeders;

use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\PriceListItem;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductCategory;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\ProductVariantValue;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\VariantAttribute;
use App\Modules\Inventory\Domain\Models\VariantAttributeValue;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $this->seedWarehouses($now);
        $this->seedCategories();
        $this->seedProducts($now);
        $this->seedPriceLists();
    }

    protected function seedWarehouses(Carbon $now): void
    {
        $definitions = [
            1 => [
                ['code' => 'ACM-MAIN', 'name' => 'ACME Ana Depo', 'bins' => ['A1', 'B1', 'C1']],
                ['code' => 'ACM-SEC', 'name' => 'ACME Yedek Depo', 'bins' => ['A2', 'B2', 'C2']],
            ],
            2 => [
                ['code' => 'BET-MAIN', 'name' => 'BETA Ana Depo', 'bins' => ['A1', 'B1', 'C1']],
                ['code' => 'BET-SEC', 'name' => 'BETA Şube Depo', 'bins' => ['A2', 'B2', 'C2']],
            ],
        ];

        foreach ($definitions as $companyId => $warehouses) {
            foreach ($warehouses as $warehouseData) {
                $warehouse = Warehouse::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $warehouseData['code'],
                    ],
                    [
                        'name' => $warehouseData['name'],
                        'is_active' => true,
                        'status' => 'active',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                foreach ($warehouseData['bins'] as $binCode) {
                    WarehouseBin::query()->updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'warehouse_id' => $warehouse->id,
                            'code' => $binCode,
                        ],
                        [
                            'name' => sprintf('%s Raf %s', $warehouse->code, $binCode),
                        ]
                    );
                }
            }
        }
    }

    protected function seedCategories(): void
    {
        $categories = [
            1 => [
                ['code' => 'ACM-RAW', 'name' => 'ACME Hammaddeler'],
                ['code' => 'ACM-FIN', 'name' => 'ACME Mamuller'],
                ['code' => 'ACM-PKG', 'name' => 'ACME Paketleme'],
            ],
            2 => [
                ['code' => 'BET-RAW', 'name' => 'BETA Hammaddeler'],
                ['code' => 'BET-FIN', 'name' => 'BETA Mamuller'],
            ],
        ];

        foreach ($categories as $companyId => $items) {
            foreach ($items as $index => $category) {
                $parentId = null;

                if ($index > 0) {
                    $parentCode = $items[0]['code'];
                    $parentId = ProductCategory::query()
                        ->where('company_id', $companyId)
                        ->where('code', $parentCode)
                        ->value('id');
                }

                ProductCategory::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'code' => $category['code'],
                    ],
                    [
                        'name' => $category['name'],
                        'parent_id' => $parentId,
                        'slug' => Str::slug($category['name']),
                        'status' => 'active',
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    protected function seedProducts(Carbon $now): void
    {
        $attributeMap = [];
        foreach ([1, 2] as $companyId) {
            $color = VariantAttribute::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => 'color',
                ],
                [
                    'name' => 'Renk',
                ]
            );

            $size = VariantAttribute::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'code' => 'size',
                ],
                [
                    'name' => 'Beden',
                ]
            );

            $attributeMap[$companyId] = [
                'color' => [
                    'attribute' => $color,
                    'values' => [
                        'kirmizi' => VariantAttributeValue::query()->updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'attribute_id' => $color->id,
                                'code' => 'kirmizi',
                            ],
                            ['value' => 'Kırmızı']
                        ),
                        'mavi' => VariantAttributeValue::query()->updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'attribute_id' => $color->id,
                                'code' => 'mavi',
                            ],
                            ['value' => 'Mavi']
                        ),
                    ],
                ],
                'size' => [
                    'attribute' => $size,
                    'values' => [
                        's' => VariantAttributeValue::query()->updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'attribute_id' => $size->id,
                                'code' => 's',
                            ],
                            ['value' => 'S']
                        ),
                        'm' => VariantAttributeValue::query()->updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'attribute_id' => $size->id,
                                'code' => 'm',
                            ],
                            ['value' => 'M']
                        ),
                    ],
                ],
            ];
        }

        $products = [
            1 => [
                ['sku' => 'ACM-RM-01', 'name' => 'Çelik Bobin', 'category' => 'ACM-RAW', 'price' => 1200.00, 'reorder_point' => 50],
                ['sku' => 'ACM-RM-02', 'name' => 'Plastik Granül', 'category' => 'ACM-RAW', 'price' => 180.00, 'reorder_point' => 200],
                ['sku' => 'ACM-FG-01', 'name' => 'Endüstriyel Pompa', 'category' => 'ACM-FIN', 'price' => 3200.00, 'reorder_point' => 10, 'variants' => true],
                ['sku' => 'ACM-FG-02', 'name' => 'Basınç Valfi', 'category' => 'ACM-FIN', 'price' => 950.00, 'reorder_point' => 25],
                ['sku' => 'ACM-PK-01', 'name' => 'Kutu (Büyük)', 'category' => 'ACM-PKG', 'price' => 12.50, 'reorder_point' => 500],
                ['sku' => 'ACM-PK-02', 'name' => 'Koli Bandı', 'category' => 'ACM-PKG', 'price' => 4.20, 'reorder_point' => 300],
            ],
            2 => [
                ['sku' => 'BET-RM-01', 'name' => 'Pamuk Kumaş', 'category' => 'BET-RAW', 'price' => 85.00, 'reorder_point' => 150],
                ['sku' => 'BET-RM-02', 'name' => 'Düğme Seti', 'category' => 'BET-RAW', 'price' => 15.00, 'reorder_point' => 400],
                ['sku' => 'BET-FG-01', 'name' => 'Kadın Tişört', 'category' => 'BET-FIN', 'price' => 180.00, 'reorder_point' => 60, 'variants' => true],
                ['sku' => 'BET-FG-02', 'name' => 'Erkek Gömlek', 'category' => 'BET-FIN', 'price' => 220.00, 'reorder_point' => 40],
                ['sku' => 'BET-FG-03', 'name' => 'Çocuk Sweatshirt', 'category' => 'BET-FIN', 'price' => 150.00, 'reorder_point' => 30, 'variants' => true],
                ['sku' => 'BET-PK-01', 'name' => 'Mağaza Poşeti', 'category' => 'BET-FIN', 'price' => 3.50, 'reorder_point' => 500],
            ],
        ];

        foreach ($products as $companyId => $items) {
            foreach ($items as $productData) {
                $categoryId = ProductCategory::query()
                    ->where('company_id', $companyId)
                    ->where('code', $productData['category'])
                    ->value('id');

                $product = Product::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'sku' => $productData['sku'],
                    ],
                    [
                        'name' => $productData['name'],
                        'category_id' => $categoryId,
                        'price' => $productData['price'],
                        'unit' => 'adet',
                        'status' => 'active',
                        'reorder_point' => Arr::get($productData, 'reorder_point', 0),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                if (! empty($productData['variants'])) {
                    $this->seedVariants($companyId, $product, $attributeMap[$companyId]);
                }

                $this->seedOpeningStock($companyId, $product);
            }
        }
    }

    protected function seedVariants(int $companyId, Product $product, array $attributeMap): void
    {
        $colors = $attributeMap['color']['values'];
        $sizes = $attributeMap['size']['values'];
        $counter = 1;

        foreach ($colors as $colorCode => $colorValue) {
            foreach ($sizes as $sizeCode => $sizeValue) {
                $sku = sprintf('%s-%s%s', $product->sku, strtoupper($colorCode[0]), strtoupper($sizeCode));
                $variant = ProductVariant::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'product_id' => $product->id,
                        'sku' => $sku,
                    ],
                    [
                        'options' => [
                            'color' => $colorValue->value,
                            'size' => strtoupper($sizeCode),
                        ],
                        'status' => 'active',
                    ]
                );

                ProductVariantValue::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $attributeMap['color']['attribute']->id,
                    ],
                    [
                        'value_id' => $colorValue->id,
                    ]
                );

                ProductVariantValue::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $attributeMap['size']['attribute']->id,
                    ],
                    [
                        'value_id' => $sizeValue->id,
                    ]
                );

                $counter++;
            }
        }
    }

    protected function seedOpeningStock(int $companyId, Product $product): void
    {
        $warehouse = Warehouse::query()->where('company_id', $companyId)->first();
        if (! $warehouse) {
            return;
        }

        $bin = WarehouseBin::query()
            ->where('company_id', $companyId)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        $existing = StockLedgerEntry::query()
            ->where('company_id', $companyId)
            ->where('product_id', $product->id)
            ->exists();

        if ($existing) {
            return;
        }

        StockLedgerEntry::query()->create([
            'company_id' => $companyId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'bin_id' => $bin?->id,
            'qty_in' => 100,
            'qty_out' => 0,
            'reason' => 'manual',
            'ref_type' => 'seed',
            'ref_id' => null,
            'doc_no' => sprintf('OPEN-%s', $product->sku),
            'dated_at' => Carbon::now()->subDay(),
        ]);
    }

    protected function seedPriceLists(): void
    {
        $lists = [
            1 => [
                ['name' => 'ACME Standart', 'currency' => 'TRY', 'type' => 'retail'],
                ['name' => 'ACME Toptan', 'currency' => 'TRY', 'type' => 'wholesale'],
            ],
            2 => [
                ['name' => 'BETA Standart', 'currency' => 'TRY', 'type' => 'retail'],
                ['name' => 'BETA Kampanya', 'currency' => 'TRY', 'type' => 'promo'],
            ],
        ];

        foreach ($lists as $companyId => $priceLists) {
            foreach ($priceLists as $definition) {
                $priceList = PriceList::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'name' => $definition['name'],
                    ],
                    [
                        'currency' => $definition['currency'],
                        'type' => $definition['type'],
                        'active' => true,
                    ]
                );

                $products = Product::query()
                    ->where('company_id', $companyId)
                    ->orderBy('id')
                    ->take(6)
                    ->get();

                foreach ($products as $index => $product) {
                    $price = round($product->price * (1 + ($index * 0.05)), 2);

                    PriceListItem::query()->updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'price_list_id' => $priceList->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'price' => $price,
                        ]
                    );
                }
            }
        }
    }
}
