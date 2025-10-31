<?php

namespace App\Modules\Finance\Http\Controllers\Admin;

use App\Core\Support\TableKit\Filters;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Domain\Services\NumberSequencer;
use App\Modules\Finance\Domain\Services\ReceiptAllocator;
use App\Modules\Finance\Http\Requests\Admin\ReceiptApplyRequest;
use App\Modules\Finance\Http\Requests\Admin\ReceiptStoreRequest;
use App\Modules\Marketing\Domain\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly NumberSequencer $sequencer,
        private readonly ReceiptAllocator $allocator,
    ) {
        $this->authorizeResource(Receipt::class, 'receipt');
    }

    public function index(Request $request): View
    {
        $query = Receipt::query()
            ->with('customer')
            ->where('company_id', currentCompanyId())
            ->latest('received_at');

        $statusFilters = Filters::multi($request, 'status');
        $docNoFilter = Filters::scalar($request, 'doc_no');
        $customerFilter = Filters::scalar($request, 'customer');
        [$receivedFrom, $receivedTo] = Filters::range($request, 'received_at');

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('doc_no', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($customerQuery) use ($search): void {
                        $customerQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $normalizedStatuses = collect($statusFilters)
            ->filter(fn ($value) => in_array($value, ['draft', 'posted', 'reconciled'], true))
            ->values();

        if ($normalizedStatuses->count() === 1) {
            $query->where('status', $normalizedStatuses->first());
        } elseif ($normalizedStatuses->count() > 1) {
            $query->whereIn('status', $normalizedStatuses->all());
        } elseif ($request->filled('status')) {
            $legacyStatus = $request->string('status')->trim()->value();
            if (in_array($legacyStatus, ['draft', 'posted', 'reconciled'], true)) {
                $query->where('status', $legacyStatus);
                $statusFilters = [$legacyStatus];
            }
        }

        if ($docNoFilter) {
            $query->where('doc_no', 'like', '%' . $docNoFilter . '%');
        }

        if ($customerFilter) {
            $query->whereHas('customer', function ($customerQuery) use ($customerFilter): void {
                $customerQuery->where('name', 'like', '%' . $customerFilter . '%');
            });
        }

        if ($receivedFrom) {
            $query->whereDate('received_at', '>=', $receivedFrom);
        }

        if ($receivedTo) {
            $query->whereDate('received_at', '<=', $receivedTo);
        }

        $receipts = $query->paginate(20)->withQueryString();

        return view('finance::admin.receipts.index', [
            'receipts' => $receipts,
            'customers' => Customer::where('company_id', currentCompanyId())->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'status' => $statusFilters,
            ],
        ]);
    }

    public function create(): View
    {
        return view('finance::admin.receipts.create', [
            'customers' => Customer::where('company_id', currentCompanyId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ReceiptStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $companyId = currentCompanyId();

        $receipt = DB::transaction(function () use ($data, $companyId): Receipt {
            $docNo = $this->sequencer->nextReceiptNumber($companyId);

            return Receipt::create(array_merge($data, [
                'company_id' => $companyId,
                'doc_no' => $docNo,
            ]));
        });

        return redirect()->route('admin.finance.receipts.show', $receipt)
            ->with('status', __('Receipt recorded.'));
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load(['customer', 'applications.invoice']);

        return view('finance::admin.receipts.show', [
            'receipt' => $receipt,
        ]);
    }

    public function applyForm(Receipt $receipt): View
    {
        $this->authorize('apply', $receipt);
        $receipt->load(['customer', 'applications.invoice']);

        $openInvoices = Invoice::open()
            ->where('company_id', $receipt->company_id)
            ->where('customer_id', $receipt->customer_id)
            ->orderBy('issued_at')
            ->get();

        return view('finance::admin.receipts.apply', [
            'receipt' => $receipt,
            'openInvoices' => $openInvoices,
        ]);
    }

    public function apply(ReceiptApplyRequest $request, Receipt $receipt): RedirectResponse
    {
        $data = $request->validated();
        $this->allocator->apply($receipt, $data['applications']);

        return redirect()->route('admin.finance.receipts.show', $receipt)
            ->with('status', __('Receipt allocations updated.'));
    }
}
