<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Core\Contracts\SettingsReader;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\CustomerContact;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Application\Services\PricingService;
use App\Modules\Marketing\Http\Requests\StoreOrderRequest;
use App\Modules\Marketing\Http\Requests\UpdateOrderRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends \App\Http\Controllers\Controller
{
    protected array $sortable = ['order_no', 'order_date', 'created_at', 'total_amount'];

    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order');
    }

    public function index(Request $request): View
    {
        $query = Order::query()->with('customer');

        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $customerId = (int) $request->query('customer_id', 0);
        $sort = strtolower((string) $request->query('sort', 'order_date'));
        $direction = strtolower((string) $request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($search !== '') {
            $query->search($search);
        }

        if ($status !== '' && in_array($status, ['draft', 'confirmed', 'shipped', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }

        $sortKey = in_array($sort, $this->sortable, true) ? $sort : 'order_date';
        $query->orderBy($sortKey, $direction);

        /** @var LengthAwarePaginator $orders */
        $orders = $query->paginate(15)->withQueryString();

        return view('marketing::orders.index', [
            'orders' => $orders,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'customer_id' => $customerId,
                'sort' => $sortKey,
                'dir' => $direction,
            ],
        ]);
    }

    public function create(SettingsReader $settingsReader): View
    {
        $defaults = array_merge([
            'price_list_id' => null,
            'payment_terms_days' => 0,
            'tax_inclusive' => false,
        ], $settingsReader->getDefaults(currentCompanyId()));

        return view('marketing::orders.create', [
            'customers' => Customer::orderBy('name')->get(),
            'contacts' => CustomerContact::orderBy('name')->get(),
            'settingsDefaults' => $defaults,
        ]);
    }

    public function store(StoreOrderRequest $request, PricingService $pricing): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = currentCompanyId();
        $data['order_no'] = $data['order_no'] ?: Order::generateOrderNo($data['company_id']);

        $result = $pricing->calculate($data['lines']);

        $order = Order::create([
            'company_id' => $data['company_id'],
            'customer_id' => $data['customer_id'],
            'contact_id' => $data['contact_id'] ?? null,
            'order_no' => $data['order_no'],
            'order_date' => $data['order_date'],
            'due_date' => $data['due_date'] ?? null,
            'currency' => $data['currency'],
            'status' => $data['status'],
            'subtotal' => $result['subtotal'],
            'discount_total' => $result['discount_total'],
            'tax_total' => $result['tax_total'],
            'total_amount' => $result['grand_total'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->syncLines($order, $result['lines']);

        return redirect()->route('admin.marketing.orders.show', $order)
            ->with('status', __('Order created successfully.'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'contact', 'lines' => fn ($q) => $q->orderBy('sort_order')]);

        return view('marketing::orders.show', [
            'order' => $order,
        ]);
    }

    public function print(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'lines' => fn ($q) => $q->orderBy('sort_order')]);

        return view('marketing::orders.print', [
            'order' => $order,
        ]);
    }

    public function edit(Order $order, SettingsReader $settingsReader): View
    {
        $order->load(['lines' => fn ($q) => $q->orderBy('sort_order')]);

        $defaults = array_merge([
            'price_list_id' => null,
            'payment_terms_days' => 0,
            'tax_inclusive' => false,
        ], $settingsReader->getDefaults(currentCompanyId()));

        return view('marketing::orders.edit', [
            'order' => $order,
            'customers' => Customer::orderBy('name')->get(),
            'contacts' => CustomerContact::orderBy('name')->get(),
            'settingsDefaults' => $defaults,
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order, PricingService $pricing): RedirectResponse
    {
        $data = $request->validated();

        $result = $pricing->calculate($data['lines']);

        if ($order->status !== 'confirmed' && $data['status'] === 'confirmed') {
            try {
                Gate::authorize('approve', $order);
            } catch (AuthorizationException $exception) {
                throw ValidationException::withMessages([
                    'status' => $exception->getMessage() ?: __('You are not allowed to approve this order.'),
                ]);
            }
        }

        $order->update([
            'customer_id' => $data['customer_id'],
            'contact_id' => $data['contact_id'] ?? null,
            'order_no' => $data['order_no'],
            'order_date' => $data['order_date'],
            'due_date' => $data['due_date'] ?? null,
            'currency' => $data['currency'],
            'status' => $data['status'],
            'subtotal' => $result['subtotal'],
            'discount_total' => $result['discount_total'],
            'tax_total' => $result['tax_total'],
            'total_amount' => $result['grand_total'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->syncLines($order, $result['lines']);

        return redirect()->route('admin.marketing.orders.show', $order)
            ->with('status', __('Order updated successfully.'));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('admin.marketing.orders.index')
            ->with('status', __('Order removed successfully.'));
    }

    protected function syncLines(Order $order, array $lines): void
    {
        $order->lines()->delete();

        foreach ($lines as $line) {
            $order->lines()->create(array_merge($line, [
                'company_id' => $order->company_id,
            ]));
        }
    }
}
