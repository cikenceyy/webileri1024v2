<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\ApInvoice;
use App\Modules\Finance\Domain\Models\ApPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApPaymentController extends Controller
{
    public function index(): View
    {
        $payments = ApPayment::query()
            ->with('invoice')
            ->latest()
            ->paginate(15);

        return view('finance::ap_payments.index', compact('payments'));
    }

    public function create(): View
    {
        $openInvoices = ApInvoice::query()
            ->where('status', '!=', 'paid')
            ->orderByDesc('created_at')
            ->get();

        return view('finance::ap_payments.create', compact('openInvoices'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ap_invoice_id' => ['required', 'integer', 'exists:ap_invoices,id'],
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = ApInvoice::query()->with(['lines', 'payments'])->findOrFail($data['ap_invoice_id']);
        $invoice->refreshTotals();

        if ((float) $data['amount'] - (float) $invoice->balance_due > 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Ödeme tutarı bakiye tutarını aşamaz.',
            ]);
        }

        DB::transaction(function () use ($invoice, $data): void {
            ApPayment::query()->create([
                'ap_invoice_id' => $invoice->id,
                'paid_at' => $data['paid_at'],
                'amount' => $data['amount'],
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->refresh();
            $invoice->refreshTotals();

            if ($invoice->balance_due <= 0.01) {
                $invoice->status = 'paid';
            } elseif ($invoice->status === 'draft') {
                $invoice->status = 'approved';
            }

            $invoice->save();
        });

        return redirect()
            ->route('admin.finance.ap-payments.index')
            ->with('status', 'Ödeme kaydedildi.');
    }
}
