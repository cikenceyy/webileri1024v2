<?php

namespace App\Modules\Logistics\Http\Controllers\Admin;

use App\Core\Contracts\SettingsReader;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Domain\Models\ShipmentLine;
use App\Modules\Logistics\Domain\Services\LogisticsSequencer;
use App\Modules\Logistics\Domain\Services\ShipmentPacker;
use App\Modules\Logistics\Domain\Services\ShipmentPicker;
use App\Modules\Logistics\Domain\Services\ShipmentShipper;
use App\Modules\Logistics\Http\Requests\Admin\ShipmentPackRequest;
use App\Modules\Logistics\Http\Requests\Admin\ShipmentPickRequest;
use App\Modules\Logistics\Http\Requests\Admin\ShipmentShipRequest;
use App\Modules\Logistics\Http\Requests\Admin\ShipmentStoreRequest;
use App\Modules\Logistics\Http\Requests\Admin\ShipmentUpdateRequest;
use App\Modules\Marketing\Domain\Models\Customer;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LogicException;

class ShipmentController extends Controller
{
    public function __construct(
        private readonly LogisticsSequencer $sequencer,
        private readonly ShipmentPicker $picker,
        private readonly ShipmentPacker $packer,
        private readonly ShipmentShipper $shipper,
        private readonly SettingsReader $settingsReader,
        private readonly Dispatcher $events,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Shipment::class);

        $companyId = currentCompanyId();
        $status = $request->string('status')->toString();

        $shipments = Shipment::query()
            ->with(['customer'])
            ->where('company_id', $companyId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('logistics::admin.shipments.index', [
            'shipments' => $shipments,
            'filters' => ['status' => $status],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Shipment::class);

        $companyId = currentCompanyId();
        $settings = $this->settingsReader->get($companyId);
        $defaults = [
            'warehouse_id' => Arr::get($settings->defaults, 'shipment_warehouse_id'),
        ];

        return view('logistics::admin.shipments.create', [
            'customers' => Customer::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'products' => Product::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'bins' => WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get(),
            'defaults' => $defaults,
        ]);
    }

    public function store(ShipmentStoreRequest $request): RedirectResponse
    {
        $companyId = currentCompanyId();
        $data = $request->validated();

        $shipment = DB::transaction(function () use ($companyId, $data) {
            $docNo = $this->sequencer->nextShipment($companyId);

            $shipment = Shipment::create([
                'company_id' => $companyId,
                'doc_no' => $docNo,
                'customer_id' => $data['customer_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'status' => 'draft',
                'packages_count' => $data['packages_count'] ?? null,
                'gross_weight' => $data['gross_weight'] ?? null,
                'net_weight' => $data['net_weight'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $index => $line) {
                ShipmentLine::create([
                    'company_id' => $companyId,
                    'shipment_id' => $shipment->id,
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'] ?? null,
                    'qty' => $line['qty'],
                    'uom' => $line['uom'] ?? 'pcs',
                    'notes' => $line['notes'] ?? null,
                    'sort' => $index,
                ]);
            }

            return $shipment;
        });

        return redirect()->route('admin.logistics.shipments.show', $shipment)
            ->with('status', __('Shipment created.'));
    }

    public function show(Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->load(['lines.product', 'lines.variant', 'lines.warehouse', 'lines.bin', 'customer']);

        $companyId = currentCompanyId();
        $settings = $this->settingsReader->get($companyId);

        return view('logistics::admin.shipments.show', [
            'shipment' => $shipment,
            'customers' => Customer::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'products' => Product::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'bins' => WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get(),
            'defaults' => [
                'warehouse_id' => Arr::get($settings->defaults, 'shipment_warehouse_id'),
            ],
        ]);
    }

    public function edit(Shipment $shipment): View
    {
        $this->authorize('update', $shipment);

        if (in_array($shipment->status, ['shipped', 'closed', 'cancelled'], true)) {
            throw new LogicException('Shipped or closed shipments cannot be edited.');
        }

        $shipment->load(['lines.product', 'lines.variant']);
        $companyId = currentCompanyId();

        return view('logistics::admin.shipments.edit', [
            'shipment' => $shipment,
            'customers' => Customer::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'products' => Product::query()->where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }

    public function update(ShipmentUpdateRequest $request, Shipment $shipment): RedirectResponse
    {
        if (in_array($shipment->status, ['shipped', 'closed', 'cancelled'], true)) {
            throw new LogicException('Shipped or closed shipments cannot be edited.');
        }

        $data = $request->validated();
        $companyId = currentCompanyId();

        DB::transaction(function () use ($shipment, $data, $companyId) {
            $shipment->update([
                'customer_id' => $data['customer_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'packages_count' => $data['packages_count'] ?? null,
                'gross_weight' => $data['gross_weight'] ?? null,
                'net_weight' => $data['net_weight'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $lineIds = [];
            foreach ($data['lines'] as $index => $lineData) {
                $lineId = $lineData['id'] ?? null;
                $payload = [
                    'company_id' => $companyId,
                    'product_id' => $lineData['product_id'],
                    'variant_id' => $lineData['variant_id'] ?? null,
                    'qty' => $lineData['qty'],
                    'uom' => $lineData['uom'] ?? 'pcs',
                    'notes' => $lineData['notes'] ?? null,
                    'sort' => $index,
                ];

                if ($lineId) {
                    $line = ShipmentLine::query()
                        ->where('company_id', $companyId)
                        ->where('shipment_id', $shipment->id)
                        ->findOrFail($lineId);
                    $line->update($payload);
                    $lineIds[] = $line->id;
                } else {
                    $newLine = ShipmentLine::create(array_merge($payload, [
                        'shipment_id' => $shipment->id,
                    ]));
                    $lineIds[] = $newLine->id;
                }
            }

            ShipmentLine::query()
                ->where('company_id', $companyId)
                ->where('shipment_id', $shipment->id)
                ->whereNotIn('id', $lineIds)
                ->delete();
        });

        return redirect()->route('admin.logistics.shipments.show', $shipment)
            ->with('status', __('Shipment updated.'));
    }

    public function startPicking(Shipment $shipment): RedirectResponse
    {
        $this->authorize('pick', $shipment);

        if (! in_array($shipment->status, ['draft', 'picking'], true)) {
            throw new LogicException('Shipment cannot be moved to picking.');
        }

        $shipment->update(['status' => 'picking']);

        return back()->with('status', __('Picking started.'));
    }

    public function pick(ShipmentPickRequest $request, Shipment $shipment): RedirectResponse
    {
        $linePayloads = collect($request->validated('lines'))
            ->mapWithKeys(fn ($line) => [$line['id'] => [
                'picked_qty' => $line['picked_qty'],
                'warehouse_id' => $line['warehouse_id'] ?? null,
                'bin_id' => $line['bin_id'] ?? null,
            ]])->all();

        $this->picker->pick($shipment, $linePayloads);

        return back()->with('status', __('Picking saved.'));
    }

    public function pack(ShipmentPackRequest $request, Shipment $shipment): RedirectResponse
    {
        $payloads = collect($request->validated('lines'))
            ->mapWithKeys(fn ($line) => [$line['id'] => [
                'packed_qty' => $line['packed_qty'],
            ]])->all();

        $data = $request->validated();

        $this->packer->pack(
            $shipment,
            $payloads,
            $data['packages_count'] ?? null,
            $data['gross_weight'] ?? null,
            $data['net_weight'] ?? null
        );

        return back()->with('status', __('Packing confirmed.'));
    }

    public function ship(ShipmentShipRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->shipper->ship($shipment);

        $this->events->dispatch(new \App\Modules\Logistics\Domain\Events\ShipmentShipped($shipment->fresh('lines')));

        return redirect()->route('admin.logistics.shipments.show', $shipment)
            ->with('status', __('Shipment marked as shipped.'));
    }

    public function close(Shipment $shipment): RedirectResponse
    {
        $this->authorize('close', $shipment);

        if (! in_array($shipment->status, ['shipped'], true)) {
            throw new LogicException('Only shipped shipments can be closed.');
        }

        $shipment->update(['status' => 'closed']);

        return back()->with('status', __('Shipment closed.'));
    }

    public function cancel(Shipment $shipment): RedirectResponse
    {
        $this->authorize('cancel', $shipment);

        if (in_array($shipment->status, ['shipped', 'closed'], true)) {
            throw new LogicException('Shipped or closed shipments cannot be cancelled.');
        }

        $shipment->update(['status' => 'cancelled']);

        return back()->with('status', __('Shipment cancelled.'));
    }

    public function print(Shipment $shipment): View
    {
        $this->authorize('print', $shipment);

        $shipment->load(['lines.product', 'lines.variant', 'customer']);
        $settings = $this->settingsReader->get(currentCompanyId());
        $template = Arr::get($settings->documents, 'shipment_note_template');

        if ($template && view()->exists($template)) {
            return view($template, ['shipment' => $shipment]);
        }

        return view('logistics::admin.shipments.print', [
            'shipment' => $shipment,
        ]);
    }
}
