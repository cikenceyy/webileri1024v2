<?php

namespace Tests\Feature\Production;

use App\Core\Contracts\SettingsReader;
use App\Core\Support\Models\Company;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\BomItem;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Models\WorkOrderIssue;
use App\Modules\Production\Domain\Models\WorkOrderReceipt;
use App\Modules\Production\Domain\Services\WorkOrderCompleter;
use App\Modules\Production\Domain\Services\WorkOrderIssuer;
use App\Modules\Production\Domain\Services\WorkOrderPlanner;
use App\Modules\Settings\Domain\SettingsDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance(SettingsReader::class, new class implements SettingsReader {
            public function get(int $companyId): SettingsDTO
            {
                return SettingsDTO::fromArray([
                    'money' => ['base_currency' => 'TRY', 'allowed_currencies' => ['TRY']],
                    'tax' => ['default_vat_rate' => 18, 'withholding_enabled' => false],
                    'sequencing' => [
                        'invoice_prefix' => 'INV',
                        'receipt_prefix' => 'RCPT',
                        'order_prefix' => 'ORD',
                        'shipment_prefix' => 'SHP',
                        'work_order_prefix' => 'WO',
                        'padding' => 4,
                        'reset_policy' => 'never',
                    ],
                    'defaults' => [
                        'payment_terms_days' => 30,
                        'warehouse_id' => null,
                        'price_list_id' => null,
                        'tax_inclusive' => false,
                        'production_issue_warehouse_id' => null,
                        'production_receipt_warehouse_id' => null,
                    ],
                    'documents' => ['invoice_print_template' => null, 'shipment_note_template' => null],
                    'general' => ['company_locale' => 'tr_TR', 'timezone' => 'Europe/Istanbul', 'decimal_precision' => 3],
                ]);
            }
        });
    }

    public function test_planner_creates_work_order_from_sales_order_line(): void
    {
        $company = Company::factory()->create();

        $product = Product::create([
            'company_id' => $company->id,
            'sku' => 'PRD-001',
            'name' => 'Montajlı Ürün',
            'price' => 100,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        $component = Product::create([
            'company_id' => $company->id,
            'sku' => 'CMP-001',
            'name' => 'Ara Parça',
            'price' => 10,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        $bom = Bom::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'code' => 'BOM-001',
            'version' => 1,
            'output_qty' => 1,
            'is_active' => true,
        ]);

        BomItem::create([
            'company_id' => $company->id,
            'bom_id' => $bom->id,
            'component_product_id' => $component->id,
            'qty_per' => 2,
            'wastage_pct' => 0,
            'sort' => 1,
        ]);

        $customer = Customer::create([
            'company_id' => $company->id,
            'code' => 'CUST-001',
            'name' => 'Test Müşterisi',
            'is_active' => true,
        ]);

        $order = SalesOrder::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'doc_no' => 'SO0001',
            'status' => SalesOrder::STATUS_CONFIRMED,
            'currency' => 'TRY',
            'tax_inclusive' => false,
            'payment_terms_days' => 30,
        ]);

        $line = SalesOrderLine::create([
            'company_id' => $company->id,
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 5,
            'uom' => 'pcs',
            'unit_price' => 100,
            'line_total' => 500,
        ]);

        /** @var WorkOrderPlanner $planner */
        $planner = app(WorkOrderPlanner::class);
        $workOrder = $planner->createFromOrderLine($line);

        $this->assertNotNull($workOrder);
        $this->assertSame('sales_order_line', $workOrder->source_type);
        $this->assertSame($line->id, $workOrder->source_id);
        $this->assertSame($bom->id, $workOrder->bom_id);
        $this->assertSame(5.0, $workOrder->target_qty);
        $this->assertTrue(str_starts_with($workOrder->doc_no, 'WO'));
    }

    public function test_issue_and_complete_flow_updates_ledger(): void
    {
        $company = Company::factory()->create();

        $finishedProduct = Product::create([
            'company_id' => $company->id,
            'sku' => 'PRD-002',
            'name' => 'Bitmiş Ürün',
            'price' => 250,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        $component = Product::create([
            'company_id' => $company->id,
            'sku' => 'CMP-002',
            'name' => 'Hammadde',
            'price' => 25,
            'unit' => 'pcs',
            'status' => 'active',
        ]);

        $bom = Bom::create([
            'company_id' => $company->id,
            'product_id' => $finishedProduct->id,
            'code' => 'BOM-002',
            'version' => 1,
            'output_qty' => 1,
            'is_active' => true,
        ]);

        BomItem::create([
            'company_id' => $company->id,
            'bom_id' => $bom->id,
            'component_product_id' => $component->id,
            'qty_per' => 1.5,
            'wastage_pct' => 10,
            'sort' => 1,
        ]);

        $issueWarehouse = Warehouse::create([
            'company_id' => $company->id,
            'code' => 'RAW',
            'name' => 'Hammadde Deposu',
            'is_active' => true,
        ]);

        $receiptWarehouse = Warehouse::create([
            'company_id' => $company->id,
            'code' => 'FG',
            'name' => 'Mamül Deposu',
            'is_active' => true,
        ]);

        $workOrder = WorkOrder::create([
            'company_id' => $company->id,
            'doc_no' => 'WO0001',
            'product_id' => $finishedProduct->id,
            'bom_id' => $bom->id,
            'target_qty' => 2,
            'uom' => 'pcs',
            'status' => 'released',
        ]);

        /** @var WorkOrderIssuer $issuer */
        $issuer = app(WorkOrderIssuer::class);
        $issuer->post($workOrder->fresh(), [[
            'component_product_id' => $component->id,
            'warehouse_id' => $issueWarehouse->id,
            'qty' => 3.3,
        ]], 1);

        $this->assertEquals(1, WorkOrderIssue::query()->count());
        $this->assertEquals(1, StockLedgerEntry::query()->where('reason', 'wo_issue')->count());

        /** @var WorkOrderCompleter $completer */
        $completer = app(WorkOrderCompleter::class);
        $completer->post($workOrder->fresh(), [
            'qty' => 2,
            'warehouse_id' => $receiptWarehouse->id,
        ], 1);

        $this->assertEquals('completed', $workOrder->fresh()->status);
        $this->assertEquals(1, WorkOrderReceipt::query()->count());
        $this->assertEquals(1, StockLedgerEntry::query()->where('reason', 'wo_receipt')->count());
    }
}
