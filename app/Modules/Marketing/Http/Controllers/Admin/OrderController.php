<?php

namespace App\Modules\Marketing\Http\Controllers\Admin;

use App\Core\Contracts\SettingsReader;
use App\Core\Domain\Sequencing\Sequencer;
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
use App\Core\Support\TableKit\Filters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly SettingsReader $settings,
        private readonly StockSignal $signal,
        private readonly Sequencer $sequencer,
    )
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $query = SalesOrder::query()->with('customer')->latest();
        $search = trim((string) $request->query('q', ''));
        $statusFilters = Filters::multi($request, 'status');
        $docNoFilter = Filters::scalar($request, 'doc_no');
        $customerFilter = Filters::scalar($request, 'customer');
        [$dueFrom, $dueTo] = Filters::range($request, 'due_date');
        [$createdFrom, $createdTo] = Filters::range($request, 'created_at');

        if ($search !== '') {
            $query->search($search);
        }

        $normalizedStatuses = collect($statusFilters)
            ->filter(fn ($value) => in_array($value, SalesOrder::statuses(), true))
            ->values();

        if ($normalizedStatuses->count() === 1) {
            $query->where('status', $normalizedStatuses->first());
        } elseif ($normalizedStatuses->count() > 1) {
            $query->whereIn('status', $normalizedStatuses->all());
        } elseif ($normalizedStatuses->isEmpty() && $request->filled('status')) {
            $legacyStatus = $request->query('status');

            if (in_array($legacyStatus, SalesOrder::statuses(), true)) {
                $query->where('status', $legacyStatus);
                $statusFilters = [$legacyStatus];
            }
        }

        if ($docNoFilter) {
            $query->where(function ($builder) use ($docNoFilter): void {
                $builder->where('doc_no', 'like', "%{$docNoFilter}%")
                    ->orWhere('order_no', 'like', "%{$docNoFilter}%");
            });
        }

        if ($customerFilter) {
            $query->whereHas('customer', function ($customerQuery) use ($customerFilter): void {
                $customerQuery->where('name', 'like', "%{$customerFilter}%");
            });
        }

        if ($dueFrom) {
            $query->whereDate('due_date', '>=', $dueFrom);
        }

        if ($dueTo) {
            $query->whereDate('due_date', '<=', $dueTo);
        }

        if ($createdFrom) {
            $query->whereDate('created_at', '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->whereDate('created_at', '<=', $createdTo);
        }

        /** @var LengthAwarePaginator $orders */
        $orders = $query->paginate()->withQueryString();

        return view('marketing::admin.orders.index', [
            'orders' => $orders,
            'filters' => [
                'q' => $search,
                'status' => $statusFilters,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', SalesOrder::class);

        $companyId = currentCompanyId();
        $settings = $this->settings->get($companyId);
        $defaults = $settings->defaults;
        $defaultTerms = $this->normalizeTerms($defaults['payment_terms_days'] ?? 0);

        $customerId = (int) $request->query('customer_id', 0);
        $customerDefaults = $customerId ? Customer::query()->find($customerId) : null;

        $payload = [
            'doc_no' => $this->nextDocNo($companyId, $settings->sequencing),
            'currency' => $settings->money['base_currency'],
            'tax_inclusive' => $defaults['tax_inclusive'],
            'payment_terms_days' => $defaultTerms,
            'due_date' => now()->addDays($defaultTerms)->toDateString(),
            'price_list_id' => $defaults['price_list_id'],
        ];

        if ($customerDefaults) {
            $customerTerms = $this->normalizeTerms($customerDefaults->payment_terms_days ?? $payload['payment_terms_days']);
            $payload['customer_id'] = $customerDefaults->id;
            $payload['price_list_id'] = $customerDefaults->default_price_list_id ?? $payload['price_list_id'];
            $payload['payment_terms_days'] = $customerTerms;
            $payload['due_date'] = now()->addDays($customerTerms)->toDateString();
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

            $terms = $this->normalizeTerms($data['payment_terms_days'] ?? ($customer->payment_terms_days ?? $settings->defaults['payment_terms_days']));
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

            $terms = $this->normalizeTerms($data['payment_terms_days']);
            $computedDue = $order->ordered_at
                ? $order->ordered_at->copy()->addDays($terms)->toDateString()
                : $order->due_date;
            $dueDate = $data['due_date'] ?? $computedDue;

            $order->update([
                'price_list_id' => $data['price_list_id'] ?? $order->price_list_id,
                'currency' => $data['currency'],
                'tax_inclusive' => $data['tax_inclusive'],
                'payment_terms_days' => $terms,
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
        if ($order->isConfirmed()) {
            $this->authorize('view', $order);

            return redirect()
                ->route('admin.marketing.orders.show', $order)
                ->with('status', __('Sipariş zaten onaylandı.'));
        }

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
        $prefix = (string) ($sequencing['order_prefix'] ?? 'SO');
        if ($prefix === '') {
            $prefix = 'SO';
        }

        $padding = $this->padding($sequencing['padding'] ?? 6);

        if (! config('features.sequencer.v2', true)) {
            return $this->legacyDocNo($companyId, $prefix, $padding);
        }

        return $this->sequencer->next(
            $companyId,
            'sales_order',
            $prefix,
            $padding,
            $this->resetPolicy($sequencing)
        );
    }

    protected function legacyDocNo(int $companyId, string $prefix, int $padding): string
    {
        $nextNumber = (int) (SalesOrder::where('company_id', $companyId)->max('id') ?? 0) + 1;

        return $prefix . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    protected function padding(mixed $value): int
    {
        $padding = (int) $value;

        return max(3, min(8, $padding));
    }

    protected function resetPolicy(array $sequencing): string
    {
        $policy = (string) ($sequencing['reset_policy'] ?? 'yearly');

        return in_array($policy, ['yearly', 'never'], true) ? $policy : 'yearly';
    }

    protected function priceLists()
    {
        return PriceList::query()
            ->where('company_id', currentCompanyId())
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    protected function normalizeTerms(mixed $value): int
    {
        $terms = (int) $value;

        return max(0, min(180, $terms));
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
