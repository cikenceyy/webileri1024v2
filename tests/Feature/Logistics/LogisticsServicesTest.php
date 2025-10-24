<?php

namespace Tests\Feature\Logistics;

use App\Core\Contracts\SettingsReader;
use App\Core\Support\Models\Company;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\GoodsReceiptLine;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Models\ShipmentLine;
use App\Modules\Logistics\Domain\Services\ReceiptPoster;
use App\Modules\Logistics\Domain\Services\ReceiptReconciler;
use App\Modules\Logistics\Domain\Services\ShipmentPacker;
use App\Modules\Logistics\Domain\Services\ShipmentPicker;
use App\Modules\Logistics\Domain\Services\ShipmentShipper;
use App\Modules\Settings\Domain\SettingsDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsServicesTest extends TestCase
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
                        'grn_prefix' => 'GRN',
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
                        'shipment_warehouse_id' => null,
                        'receipt_warehouse_id' => null,
                    ],
                    'documents' => [
                        'invoice_print_template' => null,
                        'shipment_note_template' => null,
                        'grn_note_template' => null,
                    ],
                    'general' => [
                        'company_locale' => 'tr_TR',
                        'timezone' => 'Europe/Istanbul',
                        'decimal_precision' => 3,
                    ],
                ]);
            }
        });
    }

    public function test_shipment_pick_pack_ship_flow_updates_ledger(): void
    {
        $company = Company::factory()->create();
        app()->instance('company', $company);
        view()->share('company', $company);

        $warehouse = Warehouse::create([
            'company_id' => $company->id,
            'code' => 'DEP-01',
            'name' => 'Merkez Depo',
            'is_active' => true,
        ]);

        $bin = WarehouseBin::create([
            'company_id' => $company->id,
            'warehouse_id' => $warehouse->id,
            'code' => 'RAF-A',
            'name' => 'Raf A',
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'sku' => 'SKU-LOG-1',
            'name' => 'Sevkiyat Ürünü',
            'unit' => 'adet',
            'status' => 'active',
            'price' => 50,
        ]);

        $shipment = Shipment::create([
            'company_id' => $company->id,
            'doc_no' => 'SHP0001',
            'status' => 'draft',
            'warehouse_id' => $warehouse->id,
        ]);

        $line = ShipmentLine::create([
            'company_id' => $company->id,
            'shipment_id' => $shipment->id,
            'product_id' => $product->id,
            'qty' => 5,
            'uom' => 'adet',
            'picked_qty' => 0,
            'packed_qty' => 0,
            'shipped_qty' => 0,
        ]);

        /** @var ShipmentPicker $picker */
        $picker = app(ShipmentPicker::class);
        $picker->pick($shipment, [
            $line->id => [
                'picked_qty' => 5,
                'warehouse_id' => $warehouse->id,
                'bin_id' => $bin->id,
            ],
        ]);

        $shipment->refresh();
        $this->assertSame('picking', $shipment->status);

        /** @var ShipmentPacker $packer */
        $packer = app(ShipmentPacker::class);
        $packer->pack($shipment, [
            $line->id => ['packed_qty' => 5],
        ], 1, 12.5, 11.0);

        $shipment->refresh();
        $this->assertSame('packed', $shipment->status);

        /** @var ShipmentShipper $shipper */
        $shipper = app(ShipmentShipper::class);
        $shipper->ship($shipment);

        $shipment->refresh();
        $this->assertSame('shipped', $shipment->status);
        $this->assertNotNull($shipment->shipped_at);

        $line->refresh();
        $this->assertSame(5.0, (float) $line->shipped_qty);

        $entries = StockLedgerEntry::query()
            ->where('company_id', $company->id)
            ->where('ref_type', 'shipment')
            ->where('ref_id', $shipment->id)
            ->get();

        $this->assertCount(1, $entries);
        $this->assertSame(5.0, (float) $entries->first()->qty_out);
        $this->assertSame($warehouse->id, $entries->first()->warehouse_id);

        $this->expectException(\LogicException::class);
        $shipper->ship($shipment);
    }

    public function test_receipt_receive_and_reconcile_flow(): void
    {
        $company = Company::factory()->create();
        app()->instance('company', $company);
        view()->share('company', $company);

        $warehouse = Warehouse::create([
            'company_id' => $company->id,
            'code' => 'DEP-02',
            'name' => 'İkincil Depo',
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'sku' => 'SKU-LOG-2',
            'name' => 'Hammadde',
            'unit' => 'adet',
            'status' => 'active',
            'price' => 15,
        ]);

        $receipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'doc_no' => 'GRN0001',
            'status' => 'draft',
        ]);

        $line = GoodsReceiptLine::create([
            'company_id' => $company->id,
            'receipt_id' => $receipt->id,
            'product_id' => $product->id,
            'qty_expected' => 5,
            'qty_received' => 0,
        ]);

        /** @var ReceiptPoster $poster */
        $poster = app(ReceiptPoster::class);
        $poster->receive($receipt, [
            $line->id => [
                'qty_expected' => 5,
                'qty_received' => 4,
                'warehouse_id' => $warehouse->id,
            ],
        ], $warehouse->id);

        $receipt->refresh();
        $this->assertSame('received', $receipt->status);
        $this->assertNotNull($receipt->received_at);

        $line->refresh();
        $this->assertSame(4.0, (float) $line->qty_received);

        $entries = StockLedgerEntry::query()
            ->where('company_id', $company->id)
            ->where('ref_type', 'goods_receipt')
            ->where('ref_id', $receipt->id)
            ->get();

        $this->assertCount(1, $entries);
        $this->assertSame(4.0, (float) $entries->first()->qty_in);
        $this->assertSame($warehouse->id, $entries->first()->warehouse_id);

        /** @var ReceiptReconciler $reconciler */
        $reconciler = app(ReceiptReconciler::class);
        $reconciler->reconcile($receipt->fresh(), [
            $line->id => [
                'variance_reason' => 'vendor_short',
                'notes' => 'Tedarikçi eksik gönderdi',
            ],
        ]);

        $receipt->refresh();
        $this->assertSame('reconciled', $receipt->status);
        $this->assertSame('vendor_short', $receipt->lines()->first()->variance_reason);
    }
}
