<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Core\Contracts\SettingsReader;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use App\Modules\Marketing\Domain\StockSignal;
use App\Modules\Marketing\Http\Requests\Admin\CancelSalesOrderRequest;
use App\Modules\Marketing\Http\Requests\Admin\ConfirmSalesOrderRequest;
use App\Modules\Marketing\Http\Requests\Admin\StoreSalesOrderRequest;
use App\Modules\Marketing\Http\Requests\Admin\UpdateSalesOrderRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly SettingsReader $settings, private readonly StockSignal $signal)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $query = SalesOrder::query()->with('customer')->latest();
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        if ($search !== '') {
            $query->search($search);
        }

        if ($status && in_array($status, SalesOrder::statuses(), true)) {
            $query->where('status', $status);
        }

        /** @var LengthAwarePaginator $orders */
        $orders = $query->paginate()->withQueryString();

        return view('marketing::admin.orders.index', [
            'orders' => $orders,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SalesOrder::class);

        $companyId = currentCompanyId();
        $settings = $this->settings->get($companyId);
        $defaults = $settings->defaults;

        $customerId = (int) $request->query('customer_id', 0);
        $customerDefaults = $customerId ? Customer::query()->find($customerId) : null;

        $payload = [
            'doc_no' => $this->nextDocNo($companyId, $settings->sequencing),
            'currency' => $settings->money['base_currency'],
            'tax_inclusive' => $defaults['tax_inclusive'],
            'payment_terms_days' => $defaults['payment_terms_days'],
            'due_date' => now()->addDays($defaults['payment_terms_days'])->toDateString(),
            'price_list_id' => $defaults['price_list_id'],
        ];

        if ($customerDefaults) {
            $payload['customer_id'] = $customerDefaults->id;
            $payload['price_list_id'] = $customerDefaults->default_price_list_id ?? $payload['price_list_id'];
            $payload['payment_terms_days'] = $customerDefaults->payment_terms_days ?? $payload['payment_terms_days'];
            $payload['due_date'] = now()->addDays($payload['payment_terms_days'])->toDateString();
        }

        $products = $this->products();

        return view('marketing::admin.orders.create', [
            'defaults' => $payload,
            'priceLists' => $this->priceLists(),
            'customers' => $this->customers(),
            'products' => $products,
            'stockSignals' => $this->preloadSignals($products->pluck('id')),
        ]);
    }

    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        $companyId = currentCompanyId();
        $settings = $this->settings->get($companyId);

        DB::transaction(function () use ($request, $companyId, $settings): void {
            $data = $request->validated();
            $customer = Customer::query()->findOrFail($data['customer_id']);

            $terms = $data['payment_terms_days'] ?? ($customer->payment_terms_days ?? $settings->defaults['payment_terms_days']);
            $dueDate = $data['due_date'] ?? now()->addDays($terms)->toDateString();

            $order = SalesOrder::create([
                'company_id' => $companyId,
                'customer_id' => $customer->id,
                'price_list_id' => $data['price_list_id'] ?? $customer->default_price_list_id ?? $settings->defaults['price_list_id'],
                'doc_no' => $this->nextDocNo($companyId, $settings->sequencing),
                'status' => SalesOrder::STATUS_DRAFT,
                'currency' => $data['currency'] ?? $settings->money['base_currency'],
                'tax_inclusive' => $data['tax_inclusive'],
                'payment_terms_days' => $terms,
                'due_date' => $dueDate,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($order, $data['lines']);
        });

        return redirect()
            ->route('admin.marketing.orders.index')
            ->with('status', __('Sipariş taslağı oluşturuldu.'));
    }

    public function show(SalesOrder $order): View
    {
        $this->authorize('view', $order);

        $order->load(['customer', 'lines.product']);
        $signals = $order->lines->mapWithKeys(function (SalesOrderLine $line) use ($order): array {
            $signal = $this->signal->forProduct($order->company_id, $line->product_id);

            return [$line->id => $signal];
        });

        return view('marketing::admin.orders.show', [
            'order' => $order,
            'signals' => $signals,
        ]);
    }

    public function edit(SalesOrder $order): View
    {
        $this->authorize('update', $order);

        $order->load('lines');

        $products = $this->products();

        return view('marketing::admin.orders.edit', [
            'order' => $order,
            'priceLists' => $this->priceLists(),
            'customers' => $this->customers(),
            'products' => $products,
            'stockSignals' => $this->preloadSignals($products->pluck('id')->merge($order->lines->pluck('product_id'))),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $order): RedirectResponse
    {
        $this->authorize('update', $order);

        DB::transaction(function () use ($request, $order): void {
            $data = $request->validated();

            $terms = $data['payment_terms_days'];
            $computedDue = $order->ordered_at ? $order->ordered_at->copy()->addDays($terms)->toDateString() : $order->due_date;
            $dueDate = $data['due_date'] ?? $computedDue;

            $order->update([
                'price_list_id' => $data['price_list_id'] ?? $order->price_list_id,
                'currency' => $data['currency'],
                'tax_inclusive' => $data['tax_inclusive'],
                'payment_terms_days' => $data['payment_terms_days'],
                'due_date' => $dueDate,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncLines($order, $data['lines']);
        });

        return redirect()
            ->route('admin.marketing.orders.show', $order)
            ->with('status', __('Sipariş güncellendi.'));
    }

    public function confirm(ConfirmSalesOrderRequest $request, SalesOrder $order): RedirectResponse
    {
        $this->authorize('confirm', $order);

        $order->update([
            'status' => SalesOrder::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return redirect()
            ->route('admin.marketing.orders.show', $order)
            ->with('status', __('Sipariş onaylandı.'));
    }

    public function cancel(CancelSalesOrderRequest $request, SalesOrder $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $order->update([
            'status' => SalesOrder::STATUS_CANCELLED,
        ]);

        return redirect()
            ->route('admin.marketing.orders.show', $order)
            ->with('status', __('Sipariş iptal edildi.'));
    }

    protected function syncLines(SalesOrder $order, array $lines): void
    {
        $existing = $order->lines->keyBy('id');
        $kept = [];

        foreach ($lines as $payload) {
            $line = null;
            if (! empty($payload['id']) && $existing->has((int) $payload['id'])) {
                $line = $existing[(int) $payload['id']];
            }

            $attributes = [
                'product_id' => $payload['product_id'],
                'variant_id' => $payload['variant_id'] ?? null,
                'qty' => $payload['qty'],
                'uom' => $payload['uom'] ?? 'pcs',
                'unit_price' => $payload['unit_price'],
                'discount_pct' => $payload['discount_pct'] ?? 0,
                'tax_rate' => $payload['tax_rate'] ?? null,
                'line_total' => $this->calculateLineTotal($payload, $order->tax_inclusive),
                'notes' => $payload['notes'] ?? null,
            ];

            if ($line) {
                $line->update($attributes);
                $kept[] = $line->id;
            } else {
                $newLine = $order->lines()->create(array_merge($attributes, [
                    'company_id' => $order->company_id,
                ]));
                $kept[] = $newLine->id;
            }
        }

        $order->lines()->whereNotIn('id', $kept)->delete();
    }

    protected function calculateLineTotal(array $line, bool $taxInclusive): float
    {
        $qty = (float) ($line['qty'] ?? 0);
        $price = (float) ($line['unit_price'] ?? 0);
        $discount = (float) ($line['discount_pct'] ?? 0);
        $tax = (float) ($line['tax_rate'] ?? 0);

        $subtotal = $qty * $price;
        if ($discount > 0) {
            $subtotal -= $subtotal * ($discount / 100);
        }

        if (! $taxInclusive && $tax > 0) {
            $subtotal += $subtotal * ($tax / 100);
        }

        return round($subtotal, 2);
    }

    protected function nextDocNo(int $companyId, array $sequencing): string
    {
        $prefix = $sequencing['order_prefix'] ?? 'SO';
        $padding = (int) ($sequencing['padding'] ?? 6);
        $nextNumber = (int) (SalesOrder::where('company_id', $companyId)->max('id') ?? 0) + 1;

        return $prefix . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    protected function priceLists()
    {
        return PriceList::query()
            ->where('company_id', currentCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function customers()
    {
        return Customer::query()
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function products()
    {
        return Product::query()
            ->where('company_id', currentCompanyId())
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    protected function preloadSignals($productIds)
    {
        $companyId = currentCompanyId();

        return collect($productIds)
            ->filter()
            ->unique()
            ->mapWithKeys(function ($productId) use ($companyId) {
                $productId = (int) $productId;
                $signal = $this->signal->forProduct($companyId, $productId);

                return [$productId => $signal];
            });
    }
}
