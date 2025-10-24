<?php

namespace Tests\Feature\Logistics;

use App\Core\Support\Models\Company;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\GoodsReceiptLine;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Models\ShipmentLine;
use App\Modules\Logistics\Http\Requests\Admin\ReceiptReceiveRequest;
use App\Modules\Logistics\Http\Requests\Admin\ShipmentShipRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LogisticsRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipment_ship_request_requires_effective_warehouse(): void
    {
        $company = Company::factory()->create();
        app()->instance('company', $company);
        view()->share('company', $company);

        $product = Product::create([
            'company_id' => $company->id,
            'sku' => 'SKU-REQ-1',
            'name' => 'Test Ürünü',
            'unit' => 'adet',
            'status' => 'active',
            'price' => 10,
        ]);

        $shipment = Shipment::create([
            'company_id' => $company->id,
            'doc_no' => 'SHP1001',
            'status' => 'packed',
        ]);

        $line = ShipmentLine::create([
            'company_id' => $company->id,
            'shipment_id' => $shipment->id,
            'product_id' => $product->id,
            'qty' => 3,
            'packed_qty' => 3,
        ]);

        $request = ShipmentShipRequest::create('/admin/logistics/shipments/'.$shipment->id.'/ship', 'POST', []);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->setRouteResolver(function () use ($shipment) {
            return (new Route('POST', '/admin/logistics/shipments/{shipment}/ship', []))->setParameter('shipment', $shipment);
        });
        $request->setUserResolver(fn () => new class {
            public function can(...$arguments): bool
            {
                return true;
            }
        });
        $request->attributes->set('company_id', $company->id);

        $this->assertTrue($request->authorize());

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('lines.'.$line->id.'.warehouse_id', $validator->errors()->toArray());
    }

    public function test_receipt_receive_request_requires_effective_warehouse(): void
    {
        $company = Company::factory()->create();
        app()->instance('company', $company);
        view()->share('company', $company);

        $product = Product::create([
            'company_id' => $company->id,
            'sku' => 'SKU-REQ-2',
            'name' => 'Hammadde',
            'unit' => 'adet',
            'status' => 'active',
            'price' => 20,
        ]);

        $receipt = GoodsReceipt::create([
            'company_id' => $company->id,
            'doc_no' => 'GRN1001',
            'status' => 'draft',
        ]);

        $line = GoodsReceiptLine::create([
            'company_id' => $company->id,
            'receipt_id' => $receipt->id,
            'product_id' => $product->id,
            'qty_expected' => 5,
            'qty_received' => 0,
        ]);

        $payload = [
            'lines' => [
                [
                    'id' => $line->id,
                    'qty_expected' => 5,
                    'qty_received' => 5,
                ],
            ],
        ];

        $request = ReceiptReceiveRequest::create('/admin/logistics/receipts/'.$receipt->id.'/receive', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->setRouteResolver(function () use ($receipt) {
            return (new Route('POST', '/admin/logistics/receipts/{receipt}/receive', []))->setParameter('receipt', $receipt);
        });
        $request->setUserResolver(fn () => new class {
            public function can(...$arguments): bool
            {
                return true;
            }
        });
        $request->attributes->set('company_id', $company->id);

        $this->assertTrue($request->authorize());

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('lines.'.$line->id.'.warehouse_id', $validator->errors()->toArray());
    }
}
