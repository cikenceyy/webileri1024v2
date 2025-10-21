<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\ApInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApInvoiceController extends Controller
{
    public function index(): View
    {
        $invoices = ApInvoice::query()
            ->with(['purchaseOrder', 'goodsReceipt'])
            ->latest()
            ->paginate(15);

        return view('finance::ap_invoices.index', compact('invoices'));
    }

    public function show(ApInvoice $apInvoice): View
    {
        $apInvoice->load(['lines', 'payments', 'purchaseOrder', 'goodsReceipt']);

        return view('finance::ap_invoices.show', compact('apInvoice'));
    }

    public function update(Request $request, ApInvoice $apInvoice): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'approved', 'paid'])],
        ]);

        $status = $data['status'];

        if ($status === 'paid') {
            $apInvoice->refreshTotals();

            if ($apInvoice->balance_due > 0.01) {
                throw ValidationException::withMessages([
                    'status' => 'Ödeme tamamlanmadan fatura kapatılamaz.',
                ]);
            }
        }

        if ($status === 'approved' && $apInvoice->status === 'draft' && ! $apInvoice->invoice_date) {
            $apInvoice->invoice_date = now();
        }

        $apInvoice->status = $status;
        $apInvoice->save();

        return redirect()
            ->route('admin.finance.ap-invoices.show', $apInvoice)
            ->with('status', 'Fatura durumu güncellendi.');
    }
}
