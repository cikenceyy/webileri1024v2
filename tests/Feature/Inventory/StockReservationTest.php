<?php

use App\Core\Support\Models\Company;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('reserves stock for confirmed orders', function () {
    [$order, $stockItem] = setupOrderWithStock(qty: 10, reserved: 0, orderQty: 4);

    app(StockService::class)->reserveForOrder($order);

    expect((float) $stockItem->fresh()->reserved_qty)->toBe(4.0);
});

it('releases reserved stock when order cancelled', function () {
    [$order, $stockItem] = setupOrderWithStock(qty: 10, reserved: 5, orderQty: 3);

    app(StockService::class)->releaseReservedForOrder($order);

    expect((float) $stockItem->fresh()->reserved_qty)->toBe(2.0);
});

it('prevents reservation when available stock is insufficient', function () {
    [$order, $stockItem] = setupOrderWithStock(qty: 5, reserved: 3, orderQty: 3);

    expect(fn () => app(StockService::class)->reserveForOrder($order))
        ->toThrow(ValidationException::class, 'Rezerve edilebilir stok yetersiz.');

    expect((float) $stockItem->fresh()->reserved_qty)->toBe(3.0);
});

function setupOrderWithStock(float $qty, float $reserved, float $orderQty): array
{
    $company = Company::create([
        'name' => 'Acme Corp',
        'domain' => Str::slug(Str::random()),
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'code' => 'CUST-001',
        'name' => 'Test Customer',
        'status' => 'active',
    ]);

    $warehouse = Warehouse::create([
        'company_id' => $company->id,
        'code' => 'DEF',
        'name' => 'Default Warehouse',
        'is_default' => true,
        'status' => 'active',
    ]);

    $product = Product::create([
        'company_id' => $company->id,
        'sku' => 'SKU-001',
        'name' => 'Sample Product',
        'unit' => 'adet',
        'status' => 'active',
        'price' => 100,
    ]);

    $stockItem = StockItem::create([
        'company_id' => $company->id,
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'qty' => $qty,
        'reserved_qty' => $reserved,
    ]);

    $order = Order::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'order_no' => 'ORD-001',
        'status' => 'confirmed',
        'total_amount' => 0,
    ]);

    OrderLine::create([
        'company_id' => $company->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Line 1',
        'qty' => $orderQty,
        'unit' => 'adet',
        'unit_price' => 100,
        'line_total' => $orderQty * 100,
        'sort_order' => 1,
    ]);

    return [$order->fresh(['lines.product', 'lines.variant']), $stockItem];
}
