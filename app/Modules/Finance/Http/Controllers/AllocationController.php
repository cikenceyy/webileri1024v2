<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Allocation;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Domain\Services\AllocationService;
use App\Modules\Finance\Http\Requests\StoreAllocationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AllocationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Allocation::class);

        $query = Allocation::query()->with(['invoice', 'receipt']);

        if ($invoiceId = $request->integer('invoice_id')) {
            $query->where('invoice_id', $invoiceId);
        }

        if ($receiptId = $request->integer('receipt_id')) {
            $query->where('receipt_id', $receiptId);
        }

        $allocations = $query->latest('allocated_at')->paginate(20)->withQueryString();

        return view('finance::allocations.index', [
            'allocations' => $allocations,
            'filters' => $request->only(['invoice_id', 'receipt_id']),
        ]);
    }

    public function store(StoreAllocationRequest $request, AllocationService $service): RedirectResponse
    {
        $this->authorize('create', Allocation::class);

        $data = $request->validated();

        $invoice = Invoice::findOrFail($data['invoice_id']);
        $receipt = Receipt::findOrFail($data['receipt_id']);

        $service->allocate($receipt, $invoice, (float) $data['amount']);

        return redirect()->back()->with('status', __('Allocation applied.'));
    }

    public function destroy(Allocation $allocation): RedirectResponse
    {
        $this->authorize('delete', $allocation);

        $invoice = $allocation->invoice;
        $receipt = $allocation->receipt;

        $allocation->delete();

        $invoice->load('lines', 'allocations');
        $invoice->refreshTotals();
        $invoice->save();

        $receipt->refreshAllocatedTotal();

        return redirect()->back()->with('status', __('Allocation removed.'));
    }
}
