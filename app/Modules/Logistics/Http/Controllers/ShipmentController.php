<?php

namespace App\Modules\Logistics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Logistics\Domain\Models\Shipment;
use App\Modules\Logistics\Http\Requests\StoreShipmentRequest;
use App\Modules\Logistics\Http\Requests\UpdateShipmentRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Shipment::class, 'shipment');
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->query('q'),
            'status' => $request->query('status'),
            'carrier' => $request->query('carrier'),
            'customer_id' => $request->query('customer_id'),
            'order_id' => $request->query('order_id'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'sort' => $request->query('sort'),
            'dir' => $request->query('dir'),
        ];

        $query = Shipment::query()->with(['customer', 'order.customer']);

        $query->search($filters['q']);

        if (in_array($filters['status'], ['draft', 'preparing', 'in_transit', 'delivered', 'cancelled'], true)) {
            $query->where('status', $filters['status']);
        }

        if ($filters['carrier']) {
            $query->where('carrier', 'like', '%' . $filters['carrier'] . '%');
        }

        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if ($filters['order_id']) {
            $query->where('order_id', $filters['order_id']);
        }

        if ($filters['date_from']) {
            $query->whereDate('ship_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('ship_date', '<=', $filters['date_to']);
        }

        $sortable = ['ship_date', 'shipment_no', 'created_at', 'status', 'carrier'];
        $sort = in_array($filters['sort'], $sortable, true) ? $filters['sort'] : 'ship_date';
        $direction = $filters['dir'] === 'asc' ? 'asc' : 'desc';

        $shipments = $query
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $companyId = $this->companyId($request);

        return view('logistics::shipments.index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'customerOptions' => $this->customerOptions($companyId),
            'orderOptions' => $this->orderOptions($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $companyId = $this->companyId($request);

        return view('logistics::shipments.create', [
            'shipment' => new Shipment([
                'ship_date' => now(),
                'status' => 'draft',
            ]),
            'customerOptions' => $this->customerOptions($companyId),
            'orderOptions' => $this->orderOptions($companyId),
        ]);
    }

    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        $data = $this->preparePayload($request->validated(), $companyId);
        $data['company_id'] = $companyId;

        Shipment::create($data);

        return redirect()
            ->route('admin.logistics.shipments.index')
            ->with('status', 'Sevkiyat oluşturuldu.');
    }

    public function show(Request $request, Shipment $shipment): View
    {
        $shipment->load(['customer', 'order']);

        $invoice = null;

        if ($shipment->order_id && class_exists(\App\Modules\Finance\Domain\Models\Invoice::class)) {
            $invoice = \App\Modules\Finance\Domain\Models\Invoice::query()
                ->where('company_id', $this->companyId($request))
                ->where('order_id', $shipment->order_id)
                ->latest('issue_date')
                ->first();
        }

        return view('logistics::shipments.show', [
            'shipment' => $shipment,
            'invoice' => $invoice,
        ]);
    }

    public function print(Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->load(['customer', 'order', 'lines', 'packages']);

        return view('logistics::shipments.print', [
            'shipment' => $shipment,
        ]);
    }

    public function edit(Request $request, Shipment $shipment): View
    {
        $companyId = $this->companyId($request);

        return view('logistics::shipments.edit', [
            'shipment' => $shipment,
            'customerOptions' => $this->customerOptions($companyId),
            'orderOptions' => $this->orderOptions($companyId),
        ]);
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $companyId = $this->companyId($request);

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Şirket bağlamı bulunamadı.',
            ]);
        }

        $data = $this->preparePayload($request->validated(), $companyId, $shipment);

        $shipment->fill($data);
        $shipment->save();

        return redirect()
            ->route('admin.logistics.shipments.index')
            ->with('status', 'Sevkiyat güncellendi.');
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $shipment->delete();

        return redirect()
            ->route('admin.logistics.shipments.index')
            ->with('status', 'Sevkiyat silindi.');
    }

    protected function preparePayload(array $data, int $companyId, ?Shipment $shipment = null): array
    {
        $customerId = isset($data['customer_id']) && $data['customer_id'] !== ''
            ? (int) $data['customer_id']
            : null;
        $orderId = isset($data['order_id']) && $data['order_id'] !== ''
            ? (int) $data['order_id']
            : null;

        $customer = $this->resolveCustomer($customerId, $companyId);
        $order = $this->resolveOrder($orderId, $companyId);

        if ($order) {
            if ($customer && (int) $order->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'order_id' => 'Seçilen sipariş ile müşteri eşleşmiyor.',
                ]);
            }

            $data['customer_id'] = $customer?->id ?? $order->customer_id;
        } elseif ($customer) {
            $data['customer_id'] = $customer->id;
        } else {
            $data['customer_id'] = null;
        }

        if (! isset($data['shipment_no']) || $data['shipment_no'] === '') {
            $data['shipment_no'] = Shipment::generateNo($companyId);
        }

        $data['ship_date'] = $data['ship_date'] ?? now()->toDateString();

        foreach (['package_count', 'weight_kg', 'volume_dm3', 'shipping_cost'] as $numericField) {
            if (! array_key_exists($numericField, $data) || $data[$numericField] === '' || $data[$numericField] === null) {
                $data[$numericField] = null;
            }
        }

        $data['order_id'] = $order?->id;

        return $data;
    }

    protected function resolveCustomer(?int $customerId, int $companyId): ?Customer
    {
        if (! $customerId) {
            return null;
        }

        $customer = Customer::query()
            ->where('company_id', $companyId)
            ->find($customerId);

        if (! $customer) {
            throw ValidationException::withMessages([
                'customer_id' => 'Seçilen müşteri bulunamadı.',
            ]);
        }

        return $customer;
    }

    protected function resolveOrder(?int $orderId, int $companyId): ?Order
    {
        if (! $orderId) {
            return null;
        }

        $order = Order::query()
            ->where('company_id', $companyId)
            ->find($orderId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order_id' => 'Seçilen sipariş bulunamadı.',
            ]);
        }

        return $order;
    }

    protected function customerOptions(?int $companyId): Collection
    {
        if (! $companyId) {
            return collect();
        }

        return Customer::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Customer $customer) => [
                'value' => $customer->id,
                'label' => $customer->code . ' — ' . $customer->name,
            ]);
    }

    protected function orderOptions(?int $companyId): Collection
    {
        if (! $companyId) {
            return collect();
        }

        return Order::query()
            ->where('company_id', $companyId)
            ->with('customer:id,name,code')
            ->orderByDesc('order_date')
            ->limit(200)
            ->get(['id', 'order_no', 'customer_id'])
            ->map(function (Order $order) {
                $customerName = $order->customer?->name ?? '—';

                return [
                    'value' => $order->id,
                    'label' => $order->order_no . ' — ' . $customerName,
                ];
            });
    }

    protected function companyId(Request $request): ?int
    {
        return $request->attributes->get('company_id')
            ?? (app()->bound('company') ? app('company')->id : null);
    }
}
