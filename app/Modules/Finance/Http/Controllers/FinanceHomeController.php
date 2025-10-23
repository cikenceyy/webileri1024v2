<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\BankTransaction;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class FinanceHomeController extends Controller
{
    public function __invoke(): View
    {
        $today = Carbon::today();
        $endOfWeek = Carbon::today()->endOfWeek();

        $dueToday = $this->invoicesDueBetween($today, $today);
        $dueThisWeek = $this->invoicesDueBetween($today, $endOfWeek);
        $overdue = $this->overdueInvoices($today);

        $cashPosition = $this->cashPosition();
        $recentActivities = $this->recentActivities();

        return view('finance::home', [
            'summary' => [
                'today_total' => $dueToday->sum('balance_due'),
                'today_count' => $dueToday->count(),
                'week_total' => $dueThisWeek->sum('balance_due'),
                'week_count' => $dueThisWeek->count(),
                'overdue_total' => $overdue->sum('balance_due'),
                'overdue_count' => $overdue->count(),
                'cash_position' => $cashPosition,
            ],
            'dueToday' => $dueToday,
            'dueThisWeek' => $dueThisWeek,
            'overdue' => $overdue,
            'recentActivities' => $recentActivities,
        ]);
    }

    protected function invoicesDueBetween(Carbon $start, Carbon $end): Collection
    {
        return Invoice::query()
            ->with('customer:id,name')
            ->whereNotNull('due_date')
            ->whereIn('status', ['published', 'partial'])
            ->where('balance_due', '>', 0)
            ->whereBetween('due_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->orderBy('due_date')
            ->limit(12)
            ->get(['id', 'invoice_no', 'customer_id', 'due_date', 'balance_due', 'currency']);
    }

    protected function overdueInvoices(Carbon $reference): Collection
    {
        return Invoice::query()
            ->with('customer:id,name')
            ->whereNotNull('due_date')
            ->whereIn('status', ['published', 'partial'])
            ->where('balance_due', '>', 0)
            ->whereDate('due_date', '<', $reference)
            ->orderByDesc('due_date')
            ->limit(12)
            ->get(['id', 'invoice_no', 'customer_id', 'due_date', 'balance_due', 'currency']);
    }

    protected function cashPosition(): array
    {
        $net = (float) BankTransaction::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END), 0) as net")
            ->value('net');

        $lastMovement = BankTransaction::query()->max('transacted_at');

        return [
            'net' => $net,
            'last_movement' => $lastMovement ? Carbon::parse($lastMovement) : null,
        ];
    }

    protected function recentActivities(): Collection
    {
        $invoices = Invoice::query()
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'invoice_no', 'updated_at', 'grand_total', 'currency']);

        $receipts = Receipt::query()
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'receipt_no', 'updated_at', 'amount', 'currency']);

        return collect()
            ->merge($invoices->map(function (Invoice $invoice) {
                return [
                    'type' => 'invoice',
                    'label' => $invoice->invoice_no,
                    'amount' => $invoice->grand_total,
                    'currency' => $invoice->currency,
                    'at' => $invoice->updated_at,
                    'url' => route('admin.finance.invoices.show', $invoice),
                ];
            }))
            ->merge($receipts->map(function (Receipt $receipt) {
                return [
                    'type' => 'receipt',
                    'label' => $receipt->receipt_no,
                    'amount' => $receipt->amount,
                    'currency' => $receipt->currency,
                    'at' => $receipt->updated_at,
                    'url' => route('admin.finance.receipts.show', $receipt),
                ];
            }))
            ->sortByDesc('at')
            ->take(8)
            ->values();
    }
}
