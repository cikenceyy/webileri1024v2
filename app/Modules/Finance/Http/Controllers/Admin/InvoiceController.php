<?php

namespace App\Modules\Finance\Http\Controllers\Admin;

use App\Core\Contracts\SettingsReader;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\InvoiceLine;
use App\Modules\Finance\Domain\Services\InvoiceCalculator;
use App\Modules\Finance\Domain\Services\NumberSequencer;
use App\Modules\Finance\Http\Requests\Admin\InvoiceIssueRequest;
use App\Modules\Finance\Http\Requests\Admin\InvoiceStoreRequest;
use App\Modules\Finance\Http\Requests\Admin\InvoiceUpdateRequest;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly SettingsReader $settingsReader,
        private readonly InvoiceCalculator $calculator,
        private readonly NumberSequencer $sequencer,
    ) {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(Request $request): View
    {
        $query = Invoice::query()
            ->with('customer')
            ->where('company_id', currentCompanyId())
            ->latest('created_at');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('doc_no', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($search): void {
                        $customerQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($status = $request->string('status')->trim()->value()) {
            $query->where('status', $status);
        }

        if ($customer = $request->integer('customer_id')) {
            $query->where('customer_id', $customer);
        }

        $invoices = $query->paginate(20)->withQueryString();

        $metrics = [
            'draft' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_DRAFT)->count(),
            'issued' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_ISSUED)->count(),
            'partially_paid' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_PARTIALLY_PAID)->count(),
            'paid' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_PAID)->count(),
        ];

        return view('finance::admin.invoices.index', [
            'invoices' => $invoices,
            'filters' => $request->only(['q', 'status', 'customer_id']),
            'metrics' => $metrics,
            'customers' => Customer::where('company_id', currentCompanyId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        $defaults = $this->defaults();

        return view('finance::admin.invoices.create', array_merge($defaults, [
            'invoice' => new Invoice([
                'currency' => $defaults['defaults']['currency'],
                'tax_inclusive' => $defaults['defaults']['tax_inclusive'],
                'payment_terms_days' => $defaults['defaults']['payment_terms_days'],
            ]),
        ]));
    }

    public function store(InvoiceStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $companyId = currentCompanyId();
        $linesPayload = Arr::pull($data, 'lines', []);

        $calculation = $this->calculator->calculate($linesPayload, (bool) $data['tax_inclusive']);

        $invoice = DB::transaction(function () use ($data, $calculation, $companyId): Invoice {
            $invoice = Invoice::create(array_merge($data, [
                'company_id' => $companyId,
                'status' => Invoice::STATUS_DRAFT,
                'doc_no' => null,
                'subtotal' => $calculation['totals']['subtotal'],
                'tax_total' => $calculation['totals']['tax'],
                'grand_total' => $calculation['totals']['grand'],
            ]));

            foreach ($calculation['lines'] as $line) {
                $invoice->lines()->create(array_merge($line, [
                    'company_id' => $companyId,
                ]));
            }

            return $invoice;
        });

        return redirect()->route('admin.finance.invoices.show', $invoice)
            ->with('status', __('Invoice saved as draft.'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'lines', 'applications.receipt']);

        return view('finance::admin.invoices.show', [
            'invoice' => $invoice,
        ]);
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load('lines');

        return view('finance::admin.invoices.edit', array_merge($this->defaults(), [
            'invoice' => $invoice,
        ]));
    }

    public function update(InvoiceUpdateRequest $request, Invoice $invoice): RedirectResponse
    {
        if (! $invoice->isDraft()) {
            return redirect()->route('admin.finance.invoices.show', $invoice)
                ->withErrors(__('Only draft invoices can be edited.'));
        }

        $data = $request->validated();
        $companyId = currentCompanyId();
        $linesPayload = Arr::pull($data, 'lines', []);

        $calculation = $this->calculator->calculate($linesPayload, (bool) $data['tax_inclusive']);

        DB::transaction(function () use ($invoice, $data, $calculation, $companyId, $linesPayload): void {
            $invoice->update(array_merge($data, [
                'subtotal' => $calculation['totals']['subtotal'],
                'tax_total' => $calculation['totals']['tax'],
                'grand_total' => $calculation['totals']['grand'],
            ]));

            $existingIds = collect($linesPayload)->pluck('id')->filter()->all();
            if (! empty($existingIds)) {
                $invoice->lines()->whereNotIn('id', $existingIds)->delete();
            } else {
                $invoice->lines()->delete();
            }

            foreach ($calculation['lines'] as $line) {
                $lineId = $line['id'] ?? null;
                unset($line['id']);
                if ($lineId) {
                    /** @var InvoiceLine|null $existing */
                    $existing = $invoice->lines()->where('id', $lineId)->first();
                    if ($existing) {
                        $existing->fill($line)->save();
                        continue;
                    }
                }

                $invoice->lines()->create(array_merge($line, [
                    'company_id' => $companyId,
                ]));
            }
        });

        return redirect()->route('admin.finance.invoices.show', $invoice)
            ->with('status', __('Invoice updated.'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if (! $invoice->isDraft()) {
            return redirect()->route('admin.finance.invoices.show', $invoice)
                ->withErrors(__('Only draft invoices can be deleted.'));
        }

        $invoice->lines()->delete();
        $invoice->delete();

        return redirect()->route('admin.finance.invoices.index')->with('status', __('Invoice deleted.'));
    }

    public function issue(InvoiceIssueRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('issue', $invoice);

        if (! $invoice->isDraft()) {
            return redirect()->route('admin.finance.invoices.show', $invoice)
                ->withErrors(__('Only draft invoices can be issued.'));
        }

        $data = $request->validated();
        $issuedAt = isset($data['issued_at']) ? Carbon::parse($data['issued_at']) : now();
        $terms = (int) ($data['payment_terms_days'] ?? $invoice->payment_terms_days);

        $docNo = $invoice->doc_no ?: $this->sequencer->nextInvoiceNumber(currentCompanyId());
        $invoice->markIssued($docNo, $issuedAt, $terms);

        return redirect()->route('admin.finance.invoices.show', $invoice)
            ->with('status', __('Invoice issued.'));
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        $this->authorize('cancel', $invoice);

        if (! $invoice->isIssued()) {
            return redirect()->route('admin.finance.invoices.show', $invoice)
                ->withErrors(__('Only issued invoices can be cancelled.'));
        }

        if ($invoice->paid_amount > 0) {
            return redirect()->route('admin.finance.invoices.show', $invoice)
                ->withErrors(__('Cannot cancel an invoice with payments applied.'));
        }

        $invoice->forceFill([
            'status' => Invoice::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ])->save();

        return redirect()->route('admin.finance.invoices.show', $invoice)
            ->with('status', __('Invoice cancelled.'));
    }

    public function print(Invoice $invoice): View
    {
        $this->authorize('print', $invoice);

        $invoice->load(['customer', 'lines', 'applications.receipt']);
        $settings = $this->settingsReader->get(currentCompanyId());

        return view('finance::admin.invoices.print', [
            'invoice' => $invoice,
            'template' => $settings->documents['invoice_print_template'],
        ]);
    }

    public function createFromOrder(SalesOrder $order): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        if ($order->company_id !== currentCompanyId() || ! $order->isConfirmed()) {
            abort(403);
        }

        $order->load('lines.product');
        $defaults = $this->defaults();
        $lines = [];
        foreach ($order->lines as $index => $line) {
            $lines[] = [
                'product_id' => $line->product_id,
                'variant_id' => $line->variant_id,
                'description' => $line->product?->name ?: __('Order Line #:number', ['number' => $index + 1]),
                'qty' => $line->qty,
                'uom' => $line->uom,
                'unit_price' => $line->unit_price,
                'discount_pct' => $line->discount_pct,
                'tax_rate' => $line->tax_rate,
            ];
        }

        $payload = [
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'currency' => $order->currency ?: $defaults['defaults']['currency'],
            'tax_inclusive' => (bool) $order->tax_inclusive,
            'payment_terms_days' => $order->payment_terms_days ?: $defaults['defaults']['payment_terms_days'],
            'notes' => $order->notes,
        ];

        $calculation = $this->calculator->calculate($lines, (bool) $payload['tax_inclusive']);

        $invoice = DB::transaction(function () use ($payload, $calculation): Invoice {
            $invoice = Invoice::create(array_merge($payload, [
                'company_id' => currentCompanyId(),
                'status' => Invoice::STATUS_DRAFT,
                'doc_no' => null,
                'subtotal' => $calculation['totals']['subtotal'],
                'tax_total' => $calculation['totals']['tax'],
                'grand_total' => $calculation['totals']['grand'],
            ]));

            foreach ($calculation['lines'] as $line) {
                $invoice->lines()->create(array_merge($line, [
                    'company_id' => currentCompanyId(),
                ]));
            }

            return $invoice;
        });

        return redirect()->route('admin.finance.invoices.show', $invoice)
            ->with('status', __('Invoice created from order.'));
    }

    protected function defaults(): array
    {
        $companyId = currentCompanyId();
        $settings = $this->settingsReader->get($companyId);

        return [
            'customers' => Customer::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'orders' => SalesOrder::where('company_id', $companyId)
                ->where('status', SalesOrder::STATUS_CONFIRMED)
                ->latest('confirmed_at')
                ->take(25)
                ->get(['id', 'doc_no']),
            'defaults' => [
                'currency' => $settings->money['base_currency'],
                'payment_terms_days' => $settings->defaults['payment_terms_days'],
                'tax_inclusive' => $settings->defaults['tax_inclusive'],
            ],
            'tax_rate_default' => $settings->tax['default_vat_rate'],
        ];
    }
}
