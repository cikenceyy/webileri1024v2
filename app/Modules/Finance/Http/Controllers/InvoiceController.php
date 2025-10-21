<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\InvoiceLine;
use App\Modules\Finance\Domain\Services\BillingService;
use App\Modules\Finance\Http\Requests\StoreInvoiceRequest;
use App\Modules\Finance\Http\Requests\UpdateInvoiceRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(Request $request): View
    {
        $query = Invoice::query()->with(['customer']);

        if ($search = $request->string('q')) {
            $query->where(function ($q) use ($search): void {
                $like = '%' . $search . '%';
                $q->where('invoice_no', 'like', $like)
                    ->orWhereHas('customer', static fn ($c) => $c->where('name', 'like', $like));
            });
        }

        if ($status = $request->string('status')) {
            $query->where('status', $status);
        }

        if ($customerId = $request->integer('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $invoices = $query->latest('issue_date')->paginate(15)->withQueryString();

        return view('finance::invoices.index', [
            'invoices' => $invoices,
            'filters' => $request->only(['q', 'status', 'customer_id']),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        return view('finance::invoices.create', $this->formData());
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $request->validated();
        $lines = $data['lines'];
        unset($data['lines']);

        $invoiceNo = $data['invoice_no'] ?? Invoice::generateInvoiceNo(currentCompanyId());

        $invoice = Invoice::create(array_merge($data, [
            'invoice_no' => $invoiceNo,
            'created_by' => optional(auth()->user())->id,
        ]));

        $this->syncLines($invoice, $lines);

        $invoice->load(['lines', 'allocations']);
        $invoice->refreshTotals();
        $invoice->save();

        return redirect()->route('admin.finance.invoices.show', $invoice)->with('status', __('Invoice created successfully.'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['lines', 'allocations.receipt', 'customer']);

        return view('finance::invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function print(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['lines', 'customer']);

        return view('finance::invoices.print', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('lines');

        return view('finance::invoices.edit', array_merge($this->formData(), [
            'invoice' => $invoice,
        ]));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validated();
        $lines = $data['lines'];
        unset($data['lines']);

        if (($data['status'] ?? null) === 'published' && $invoice->status !== 'published') {
            try {
                Gate::authorize('publish', $invoice);
            } catch (AuthorizationException $exception) {
                throw ValidationException::withMessages([
                    'status' => $exception->getMessage() ?: __('You are not allowed to publish this invoice.'),
                ]);
            }
        }

        $invoice->update($data);

        $this->syncLines($invoice, $lines);

        $invoice->load(['lines', 'allocations']);
        $invoice->refreshTotals();
        $invoice->save();

        return redirect()->route('admin.finance.invoices.show', $invoice)->with('status', __('Invoice updated successfully.'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoice->delete();

        return redirect()->route('admin.finance.invoices.index')->with('status', __('Invoice deleted.'));
    }

    public function createFromOrder(Order $order, BillingService $billingService): RedirectResponse
    {
        Gate::authorize('convertOrder', Invoice::class);

        if ($order->company_id !== currentCompanyId()) {
            abort(403);
        }

        $invoice = $billingService->fromOrder($order);

        return redirect()->route('admin.finance.invoices.show', $invoice)->with('status', __('Invoice generated from order.'));
    }

    protected function formData(): array
    {
        $companyId = currentCompanyId();

        return [
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'orders' => Order::where('company_id', $companyId)->latest('order_date')->take(50)->get(['id', 'order_no']),
            'currencies' => config('finance.supported_currencies'),
            'default_tax' => config('finance.default_tax_rate'),
        ];
    }

    protected function syncLines(Invoice $invoice, array $lines): void
    {
        $existingIds = $invoice->lines()->pluck('id')->all();
        $handled = [];

        foreach ($lines as $index => $payload) {
            $lineData = [
                'company_id' => $invoice->company_id,
                'description' => Arr::get($payload, 'description'),
                'product_id' => Arr::get($payload, 'product_id'),
                'variant_id' => Arr::get($payload, 'variant_id'),
                'qty' => Arr::get($payload, 'qty'),
                'unit' => Arr::get($payload, 'unit', 'pcs'),
                'unit_price' => Arr::get($payload, 'unit_price'),
                'discount_rate' => Arr::get($payload, 'discount_rate', 0),
                'tax_rate' => Arr::get($payload, 'tax_rate', config('finance.default_tax_rate')),
                'line_total' => $this->calculateLineTotal($payload),
                'sort_order' => $index,
            ];

            if ($lineId = Arr::get($payload, 'id')) {
                $invoiceLine = InvoiceLine::query()->where('invoice_id', $invoice->id)->findOrFail($lineId);
                $invoiceLine->update($lineData);
                $handled[] = $invoiceLine->id;
            } else {
                $lineData['invoice_id'] = $invoice->id;
                $invoiceLine = InvoiceLine::create($lineData);
                $handled[] = $invoiceLine->id;
            }
        }

        $toDelete = array_diff($existingIds, $handled);

        if ($toDelete) {
            InvoiceLine::whereIn('id', $toDelete)->delete();
        }
    }

    protected function calculateLineTotal(array $line): float
    {
        $base = (float) ($line['qty'] ?? 0) * (float) ($line['unit_price'] ?? 0);
        $discount = $base * ((float) ($line['discount_rate'] ?? 0) / 100);
        $net = $base - $discount;
        $tax = $net * ((float) ($line['tax_rate'] ?? config('finance.default_tax_rate')) / 100);

        return round($net + $tax, 2);
    }
}
