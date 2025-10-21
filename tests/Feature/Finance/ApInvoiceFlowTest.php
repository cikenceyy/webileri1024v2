<?php

use App\Core\Bus\Events\GrnReceived;
use App\Core\Bus\Listeners\CreateApInvoiceFromGrn;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Core\Support\Models\Company;
use App\Models\User;
use App\Modules\Finance\Domain\Models\ApInvoice;
use App\Modules\Finance\Domain\Models\ApPayment;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Procurement\Domain\Models\Grn;
use App\Modules\Procurement\Domain\Models\PoLine;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function seedFinanceCompany(): array
{
    $company = Company::create([
        'name' => 'Finans Test Şirketi',
        'domain' => Str::slug(Str::random()),
    ]);

    app()->instance('company', $company);
    view()->share('company', $company);

    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $product = Product::create([
        'company_id' => $company->id,
        'sku' => 'SKU-AP-1',
        'name' => 'Parça',
        'unit' => 'adet',
        'status' => 'active',
        'price' => 20,
    ]);

    return [$company, $user, $product];
}

it('creates a draft ap invoice from goods receipt and closes it after payment', function (): void {
    [, $user, $product] = seedFinanceCompany();

    $this->withoutMiddleware([IdentifyTenant::class]);
    $this->actingAs($user);

    $this->post('/admin/procurement/pos', [
        'supplier_id' => 77,
        'currency' => 'TRY',
        'lines' => [
            [
                'product_id' => $product->id,
                'description' => 'Test kalemi',
                'qty_ordered' => 5,
                'unit' => 'adet',
                'unit_price' => 10,
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
                'qty_received' => 5,
            ],
        ],
    ])->assertRedirect();

    $grn = Grn::with('lines.poLine', 'purchaseOrder.lines')->firstOrFail();

    $listener = app(CreateApInvoiceFromGrn::class);
    $listener->handle(new GrnReceived($grn));

    $invoice = ApInvoice::with('lines')->firstOrFail();

    expect($invoice->status)->toBe('draft')
        ->and($invoice->lines)->toHaveCount(1)
        ->and($invoice->lines->first()->source_uuid)->toBe($grn->lines->first()->uuid);

    // Running the listener again should not create duplicates
    $listener->handle(new GrnReceived($grn));
    expect(ApInvoice::count())->toBe(1);

    // Introduce a variance by adjusting the invoice line price
    $line = $invoice->lines->first();
    $line->update([
        'unit_price' => $line->unit_price + 5,
        'amount' => $line->amount + 25,
    ]);

    $invoice->refresh();
    $invoice->refreshTotals();
    $invoice->save();

    expect($invoice->has_price_variance)->toBeTrue()
        ->and(abs((float) $invoice->price_variance_amount))->toBeGreaterThan(0);

    $this->post('/admin/finance/ap-payments', [
        'ap_invoice_id' => $invoice->id,
        'paid_at' => now()->toDateString(),
        'amount' => $invoice->total,
    ])->assertRedirect();

    $invoice->refresh();

    expect((float) $invoice->balance_due)->toBe(0.0)
        ->and($invoice->status)->toBe('paid')
        ->and(ApPayment::count())->toBe(1);
});
