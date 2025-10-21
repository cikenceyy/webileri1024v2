<?php

use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Core\Support\Models\Company;
use App\Models\User;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Procurement\Domain\Models\Grn;
use App\Modules\Procurement\Domain\Models\PoLine;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function seedProcurementCompany(): array
{
    $company = Company::create([
        'name' => 'Tedarik Test',
        'domain' => Str::slug(Str::random()),
    ]);

    app()->instance('company', $company);
    view()->share('company', $company);

    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $product = Product::create([
        'company_id' => $company->id,
        'sku' => 'SKU-PO-1',
        'name' => 'Hammadde',
        'unit' => 'adet',
        'status' => 'active',
        'price' => 10,
    ]);

    return [$company, $user, $product];
}

it('creates and approves a purchase order', function (): void {
    [, $user, $product] = seedProcurementCompany();

    $this->withoutMiddleware([IdentifyTenant::class]);
    $this->actingAs($user);

    $response = $this->post('/admin/procurement/pos', [
        'supplier_id' => 55,
        'currency' => 'TRY',
        'lines' => [
            [
                'product_id' => $product->id,
                'description' => 'Ana malzeme',
                'qty_ordered' => 5,
                'unit' => 'adet',
                'unit_price' => 12.5,
            ],
        ],
    ]);

    $response->assertRedirect();

    $purchaseOrder = PurchaseOrder::firstOrFail();
    expect($purchaseOrder->status)->toBe('draft')
        ->and((float) $purchaseOrder->total)->toBe(62.5);

    $this->patch("/admin/procurement/pos/{$purchaseOrder->id}", [
        'status' => 'approved',
    ])->assertRedirect();

    $purchaseOrder->refresh();
    expect($purchaseOrder->status)->toBe('approved')
        ->and($purchaseOrder->approved_at)->not->toBeNull();
});

it('records goods receipt without exceeding ordered quantity', function (): void {
    [, $user, $product] = seedProcurementCompany();

    $this->withoutMiddleware([IdentifyTenant::class]);
    $this->actingAs($user);

    $this->post('/admin/procurement/pos', [
        'supplier_id' => 42,
        'currency' => 'TRY',
        'lines' => [
            [
                'product_id' => $product->id,
                'description' => 'Test ürün',
                'qty_ordered' => 4,
                'unit' => 'adet',
                'unit_price' => 15,
            ],
        ],
    ])->assertRedirect();

    $purchaseOrder = PurchaseOrder::firstOrFail();

    $this->patch("/admin/procurement/pos/{$purchaseOrder->id}", [
        'status' => 'approved',
    ])->assertRedirect();

    $poLine = PoLine::firstOrFail();

    $this->post('/admin/procurement/grns', [
        'purchase_order_id' => $purchaseOrder->id,
        'lines' => [
            [
                'po_line_id' => $poLine->id,
                'qty_received' => 3,
            ],
        ],
    ])->assertRedirect();

    $grn = Grn::firstOrFail();
    expect($grn->status)->toBe('partial')
        ->and($purchaseOrder->fresh()->status)->toBe('approved');

    $this->post('/admin/procurement/grns', [
        'purchase_order_id' => $purchaseOrder->id,
        'lines' => [
            [
                'po_line_id' => $poLine->id,
                'qty_received' => 1,
            ],
        ],
    ])->assertRedirect();

    $purchaseOrder->refresh();
    expect($purchaseOrder->status)->toBe('closed');
});

it('rejects receipts that exceed ordered quantities', function (): void {
    [, $user, $product] = seedProcurementCompany();

    $this->withoutMiddleware([IdentifyTenant::class]);
    $this->actingAs($user);

    $this->post('/admin/procurement/pos', [
        'supplier_id' => 10,
        'currency' => 'TRY',
        'lines' => [
            [
                'product_id' => $product->id,
                'description' => 'Kısıtlı ürün',
                'qty_ordered' => 2,
                'unit' => 'adet',
                'unit_price' => 20,
            ],
        ],
    ])->assertRedirect();

    $purchaseOrder = PurchaseOrder::firstOrFail();
    $this->patch("/admin/procurement/pos/{$purchaseOrder->id}", [
        'status' => 'approved',
    ])->assertRedirect();

    $poLine = PoLine::firstOrFail();

    $response = $this->post('/admin/procurement/grns', [
        'purchase_order_id' => $purchaseOrder->id,
        'lines' => [
            [
                'po_line_id' => $poLine->id,
                'qty_received' => 3,
            ],
        ],
    ]);

    $response->assertSessionHasErrors('lines.0.qty_received');
    expect(Grn::count())->toBe(0);
});
