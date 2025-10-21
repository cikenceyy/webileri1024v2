<?php

use App\Core\Bus\Events\ShipmentDelivered;
use App\Core\Bus\Listeners\CreateInvoiceFromShipment;
use App\Core\Support\Models\Company;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('creates a single draft invoice when a shipment is delivered', function () {
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

    $product = Product::create([
        'company_id' => $company->id,
        'sku' => 'SKU-001',
        'name' => 'Sample Product',
        'unit' => 'adet',
        'status' => 'active',
        'price' => 100,
    ]);

    $order = Order::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'order_no' => 'ORD-1001',
        'status' => 'confirmed',
        'currency' => 'TRY',
        'total_amount' => 100,
    ]);

    OrderLine::create([
        'company_id' => $company->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Product Line',
        'qty' => 1,
        'unit' => 'adet',
        'unit_price' => 100,
        'line_total' => 100,
        'sort_order' => 1,
    ]);

    $shipment = Shipment::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'order_id' => $order->id,
        'shipment_no' => 'SHP-1001',
        'status' => 'delivered',
    ]);

    $listener = app(CreateInvoiceFromShipment::class);

    $listener->handle(new ShipmentDelivered($shipment));

    expect(Invoice::count())->toBe(1);

    $invoice = Invoice::first();

    expect($invoice)
        ->not->toBeNull()
        ->and($invoice->status)->toBe('draft')
        ->and($invoice->order_id)->toBe($order->id)
        ->and($invoice->company_id)->toBe($company->id);

    $listener->handle(new ShipmentDelivered($shipment));

    expect(Invoice::count())->toBe(1);
});
