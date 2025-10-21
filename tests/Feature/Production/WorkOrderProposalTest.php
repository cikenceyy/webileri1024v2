<?php

use App\Core\Bus\Events\OrderConfirmed;
use App\Core\Bus\Listeners\ProposeWorkOrderFromOrder;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Core\Support\Models\Company;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Services\WoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function seedCompany(): Company
{
    $company = Company::create([
        'name' => 'Acme Manufacturing',
        'domain' => Str::slug(Str::random()),
    ]);

    app()->instance('company', $company);
    view()->share('company', $company);

    return $company;
}

function seedOrderWithLine(Company $company): array
{
    $customer = Customer::create([
        'company_id' => $company->id,
        'code' => 'CUST-100',
        'name' => 'Test Customer',
        'status' => 'active',
    ]);

    $product = Product::create([
        'company_id' => $company->id,
        'sku' => 'SKU-100',
        'name' => 'Ürün',
        'unit' => 'adet',
        'status' => 'active',
        'price' => 50,
    ]);

    $order = Order::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'order_no' => 'ORD-1001',
        'status' => 'confirmed',
        'currency' => 'TRY',
        'total_amount' => 100,
    ]);

    $line = OrderLine::create([
        'company_id' => $company->id,
        'order_id' => $order->id,
        'product_id' => $product->id,
        'description' => 'Üretim ürünü',
        'qty' => 2,
        'unit' => 'adet',
        'unit_price' => 50,
        'line_total' => 100,
        'sort_order' => 1,
    ]);

    return [$order, $line, $product];
}

it('proposes a work order when an order is confirmed', function (): void {
    $company = seedCompany();
    [$order] = seedOrderWithLine($company);

    $listener = app(ProposeWorkOrderFromOrder::class);

    $listener->handle(new OrderConfirmed($order));

    expect(WorkOrder::count())->toBe(1);

    $workOrder = WorkOrder::first();

    expect($workOrder)
        ->not->toBeNull()
        ->and($workOrder->order_id)->toBe($order->id)
        ->and($workOrder->status)->toBe('draft')
        ->and((float) $workOrder->qty)->toBe(2.0);
});

it('marks work orders as done through the service close helper', function (): void {
    $company = seedCompany();
    [$order, $line] = seedOrderWithLine($company);

    $service = app(WoService::class);
    $service->proposeFromOrderLine($line);

    $workOrder = WorkOrder::firstOrFail();

    $service->close($workOrder);

    $workOrder->refresh();

    expect($workOrder->status)->toBe('done')
        ->and($workOrder->closed_at)->not->toBeNull();
});

it('renders the work order index page', function (): void {
    $company = seedCompany();
    [$order] = seedOrderWithLine($company);

    app(WoService::class)->proposeFromOrder($order);

    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $this->withoutMiddleware([
        IdentifyTenant::class,
    ]);

    $this->actingAs($user);

    $response = $this->get('/admin/production/work-orders');

    $response->assertOk();
    $response->assertSee($order->order_no);
});
