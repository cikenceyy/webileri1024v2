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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
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

        $filters = [
            'q' => trim((string) $request->string('q')), 
            'status' => $request->string('status')->trim()->value(),
            'customer_id' => $request->integer('customer_id'),
        ];

        if ($filters['q']) {
            $query->where(function ($builder) use ($filters): void {
                $builder->where('doc_no', 'like', '%' . $filters['q'] . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($filters): void {
                        $customerQuery->where('name', 'like', '%' . $filters['q'] . '%');
                    });
            });
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $totalCount = (clone $query)->count();
        $clientThreshold = 500;
        $pageSizeOptions = [25, 50, 100];
        $pageSize = $request->integer('per_page', 25);
        if (! in_array($pageSize, $pageSizeOptions, true)) {
            $pageSize = 25;
        }

        $mode = $totalCount <= $clientThreshold ? 'client' : 'server';

        $invoices = $mode === 'server'
            ? $query->paginate($pageSize)->withQueryString()
            : $query->limit($clientThreshold)->get();

        $dataset = [];
        if ($mode === 'client') {
            $statusBadges = [
                Invoice::STATUS_DRAFT => 'text-bg-secondary',
                Invoice::STATUS_ISSUED => 'text-bg-primary',
                Invoice::STATUS_PARTIALLY_PAID => 'text-bg-warning',
                Invoice::STATUS_PAID => 'text-bg-success',
                Invoice::STATUS_CANCELLED => 'text-bg-danger',
            ];

            $dataset = $invoices->map(function (Invoice $invoice) use ($statusBadges) {
                $docNo = $invoice->doc_no ?? __('Taslak');
                $statusLabel = Str::headline($invoice->status);
                $currency = $invoice->currency ?? config('app.currency', 'TRY');

                return [
                    'id' => (string) $invoice->getKey(),
                    'doc_no' => $docNo,
                    'doc_no_url' => route('admin.finance.invoices.show', $invoice),
                    'customer' => $invoice->customer?->name ?? '—',
                    'customer_filter' => $invoice->customer_id ? (string) $invoice->customer_id : '',
                    'status' => $statusLabel,
                    'status_badge' => $statusBadges[$invoice->status] ?? 'text-bg-secondary',
                    'status_filter' => $invoice->status,
                    'grand_total' => number_format((float) $invoice->grand_total, 2) . ' ' . $currency,
                    'grand_total_raw' => (float) $invoice->grand_total,
                    'paid_amount' => number_format((float) $invoice->paid_amount, 2) . ' ' . $currency,
                    'paid_amount_raw' => (float) $invoice->paid_amount,
                    'due_date' => optional($invoice->due_date)?->format('Y-m-d') ?? '—',
                    'due_date_raw' => optional($invoice->due_date)?->format('Y-m-d') ?? '',
                    'actions' => [
                        [
                            'label' => __('Görüntüle'),
                            'url' => route('admin.finance.invoices.show', $invoice),
                            'variant' => 'outline-primary',
                            'size' => 'sm',
                            'icon' => 'bi bi-box-arrow-up-right',
                        ],
                    ],
                    'search' => strtolower(implode(' ', array_filter([
                        $docNo,
                        $invoice->customer?->name,
                        $statusLabel,
                    ]))),
                ];
            })->all();
        }

        $metrics = [
            'draft' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_DRAFT)->count(),
            'issued' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_ISSUED)->count(),
            'partially_paid' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_PARTIALLY_PAID)->count(),
            'paid' => Invoice::where('company_id', currentCompanyId())->where('status', Invoice::STATUS_PAID)->count(),
        ];

        $customers = Customer::where('company_id', currentCompanyId())->orderBy('name')->get(['id', 'name']);

        $tableFilters = [
            [
                'key' => 'status',
                'label' => __('Durum'),
                'name' => 'status',
                'value' => $filters['status'] ?? '',
                'options' => array_merge([
                    ['value' => '', 'label' => __('Tümü')],
                ], array_map(fn ($status) => ['value' => $status, 'label' => __(Str::headline($status))], Invoice::statuses())),
                'field' => 'status_filter',
            ],
            [
                'key' => 'customer',
                'label' => __('Müşteri'),
                'name' => 'customer_id',
                'value' => $filters['customer_id'] ?? '',
                'options' => array_merge([
                    ['value' => '', 'label' => __('Tümü')],
                ], $customers->map(fn ($customer) => ['value' => (string) $customer->id, 'label' => $customer->name])->all()),
                'field' => 'customer_filter',
                'type' => 'numeric',
            ],
        ];

        $tableColumns = [
            ['key' => 'doc_no', 'label' => __('Doc No'), 'sortable' => true, 'type' => 'link', 'wrap' => false],
            ['key' => 'customer', 'label' => __('Müşteri'), 'sortable' => true],
            ['key' => 'status', 'label' => __('Durum'), 'sortable' => true, 'type' => 'badge', 'badgeKey' => 'status_badge'],
            ['key' => 'grand_total', 'label' => __('Toplam'), 'sortable' => true, 'sortKey' => 'grand_total_raw', 'align' => 'end'],
            ['key' => 'paid_amount', 'label' => __('Ödenen'), 'sortable' => true, 'sortKey' => 'paid_amount_raw', 'align' => 'end'],
            ['key' => 'due_date', 'label' => __('Vade'), 'sortable' => true, 'sortKey' => 'due_date_raw'],
            ['key' => 'actions', 'label' => __('Aksiyonlar'), 'type' => 'actions', 'align' => 'end'],
        ];

        return view('finance::admin.invoices.index', [
            'mode' => $mode,
            'invoices' => $mode === 'server' ? $invoices : null,
            'filters' => $filters,
            'metrics' => $metrics,
            'tableDataset' => $dataset,
            'tableFilters' => $tableFilters,
            'tableColumns' => $tableColumns,
            'pageSizeOptions' => $pageSizeOptions,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
            'paginatorHtml' => $mode === 'server' ? $invoices->links() : null,
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
        $templateSlug = $settings->documents['invoice_print_template'] ?? null;

        $candidates = [];
        if ($templateSlug) {
            if (Str::contains($templateSlug, '::')) {
                $candidates[] = $templateSlug;
            } else {
                $candidates[] = 'finance::admin.invoices.templates.' . $templateSlug;
                $candidates[] = 'finance::admin.invoices.' . $templateSlug;
            }
        }
        $candidates[] = 'finance::admin.invoices.print_default';

        $resolvedView = null;
        foreach ($candidates as $candidate) {
            if (View::exists($candidate)) {
                $resolvedView = $candidate;
                break;
            }
        }

        if (! $resolvedView) {
            $resolvedView = 'finance::admin.invoices.print_default';
        }

        if ($templateSlug) {
            $customFound = collect($candidates)
                ->filter(fn ($candidate) => $candidate !== 'finance::admin.invoices.print_default')
                ->contains(fn ($candidate) => View::exists($candidate));

            if (! $customFound) {
                Log::warning('Invoice print template not found, falling back to default.', [
                    'template' => $templateSlug,
                    'invoice_id' => $invoice->getKey(),
                    'company_id' => currentCompanyId(),
                ]);
            }
        }

        return view('finance::admin.invoices.print', [
            'invoice' => $invoice,
            'templateView' => $resolvedView,
            'requestedTemplate' => $templateSlug,
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
