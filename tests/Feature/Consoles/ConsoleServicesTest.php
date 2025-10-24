<?php

namespace Tests\Feature\Consoles;

use App\Consoles\Domain\CloseoutConsoleService;
use App\Consoles\Domain\QualityConsoleService;
use App\Consoles\Domain\ReplenishConsoleService;
use App\Core\Contracts\SettingsReader;
use App\Core\Support\Models\Company;
use App\Models\User;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Settings\Domain\SettingsDTO;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ConsoleServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance(SettingsReader::class, new class implements SettingsReader {
            public function get(int $companyId): SettingsDTO
            {
                return SettingsDTO::defaults();
            }

            public function getDefaults(int $companyId): array
            {
                return SettingsDTO::defaults()->defaults;
            }
        });
    }

    public function test_quality_console_records_and_summarizes_checks(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        Auth::login($user);

        $receipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'doc_no' => 'GRN-001',
            'status' => 'received',
            'received_at' => CarbonImmutable::now(),
        ]);

        $shipment = Shipment::create([
            'company_id' => $company->id,
            'doc_no' => 'SHP-001',
            'status' => 'packed',
        ]);

        $service = app(QualityConsoleService::class);

        $summary = $service->summary($company->id);
        $this->assertCount(1, $summary['incoming']);
        $this->assertCount(1, $summary['outgoing']);

        $service->record($company->id, GoodsReceipt::class, $receipt->id, 'incoming', 'fail', 'Damage');

        $updated = $service->summary($company->id);
        $this->assertSame('fail', $updated['incoming'][0]['last_check']['result']);
    }

    public function test_closeout_console_batch_print_builds_links(): void
    {
        $company = Company::factory()->create();
        $customer = Customer::create([
            'company_id' => $company->id,
            'code' => 'CUST-1',
            'name' => 'Acme Co',
            'payment_terms_days' => 30,
            'default_price_list_id' => null,
            'tax_no' => null,
            'billing_address' => [],
            'shipping_address' => [],
            'credit_limit' => null,
            'is_active' => true,
        ]);

        $shipment = Shipment::create([
            'company_id' => $company->id,
            'doc_no' => 'SHP-1001',
            'customer_id' => $customer->id,
            'status' => 'shipped',
            'shipped_at' => CarbonImmutable::now(),
        ]);

        $invoice = Invoice::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'doc_no' => 'INV-1001',
            'status' => Invoice::STATUS_ISSUED,
            'currency' => 'USD',
            'tax_inclusive' => false,
            'subtotal' => 100,
            'tax_total' => 18,
            'grand_total' => 118,
            'payment_terms_days' => 30,
            'issued_at' => CarbonImmutable::now(),
            'due_date' => CarbonImmutable::now()->addDays(30),
        ]);

        $receipt = Receipt::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'doc_no' => 'RCPT-1001',
            'received_at' => CarbonImmutable::now(),
            'amount' => 118,
        ]);

        $grn = GoodsReceipt::create([
            'company_id' => $company->id,
            'doc_no' => 'GRN-1001',
            'status' => 'received',
            'received_at' => CarbonImmutable::now(),
        ]);

        $service = app(CloseoutConsoleService::class);

        $state = $service->summary($company->id, CarbonImmutable::now()->toDateString());
        $this->assertNotEmpty($state['shipments']);
        $this->assertNotEmpty($state['invoices']);
        $this->assertNotEmpty($state['receipts']);
        $this->assertNotEmpty($state['goods_receipts']);

        $links = $service->batchPrint([
            ['type' => 'shipment', 'id' => $shipment->id],
            ['type' => 'invoice', 'id' => $invoice->id],
            ['type' => 'receipt', 'id' => $receipt->id],
            ['type' => 'goods_receipt', 'id' => $grn->id],
        ]);

        $this->assertCount(4, $links);
        $this->assertStringContainsString((string) $shipment->id, $links[0]['label']);
        $this->assertStringContainsString('logistics/shipments', $links[0]['url']);
    }

    public function test_replenish_console_posts_transfer_and_ledgers(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        Auth::login($user);

        $product = Product::create([
            'company_id' => $company->id,
            'sku' => 'SKU-1',
            'name' => 'Test Product',
            'price' => 10,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        $fromWarehouse = Warehouse::create([
            'company_id' => $company->id,
            'code' => 'WH-A',
            'name' => 'Ana Depo',
            'is_active' => true,
        ]);

        $toWarehouse = Warehouse::create([
            'company_id' => $company->id,
            'code' => 'WH-B',
            'name' => 'İkincil Depo',
            'is_active' => true,
        ]);

        $service = app(ReplenishConsoleService::class);

        $transfer = $service->createTransfer($company->id, $fromWarehouse->id, $toWarehouse->id, [
            ['product_id' => $product->id, 'qty' => 5],
        ]);

        $this->assertNotNull($transfer->doc_no);
        $this->assertEquals(2, StockLedgerEntry::where('company_id', $company->id)->count());
        $this->assertSame('posted', $transfer->fresh()->status);
    }
}
