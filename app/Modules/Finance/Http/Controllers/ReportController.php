<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function aging(Request $request): View|StreamedResponse
    {
        $data = $this->buildAgingData();

        if ($request->get('format') === 'csv') {
            return $this->agingCsv($data['invoices']);
        }

        if ($request->boolean('print')) {
            return view('finance::reports.aging-print', $data);
        }

        return view('finance::reports.aging', $data);
    }

    public function receipts(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Receipt::class);

        $query = Receipt::with('customer')->latest('receipt_date');

        if ($request->filled('date_from')) {
            $query->whereDate('receipt_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receipt_date', '<=', $request->date('date_to'));
        }

        if ($request->get('format') === 'csv') {
            $rows = $query->get();

            return $this->receiptsCsv($rows);
        }

        $receipts = $query->paginate(25)->withQueryString();

        if ($request->boolean('print')) {
            return view('finance::reports.receipts-print', [
                'receipts' => $query->get(),
                'filters' => $request->only(['date_from', 'date_to']),
            ]);
        }

        return view('finance::reports.receipts', [
            'receipts' => $receipts,
            'filters' => $request->only(['date_from', 'date_to']),
        ]);
    }

    public function summary(Request $request): View|StreamedResponse
    {
        $data = $this->buildSummaryData();

        if ($request->get('format') === 'csv') {
            return $this->summaryCsv($data['rows']);
        }

        if ($request->boolean('print')) {
            return view('finance::reports.summary-print', $data);
        }

        return view('finance::reports.summary', $data);
    }

    protected function buildAgingData(): array
    {
        $this->authorize('viewAny', Invoice::class);

        $today = now()->startOfDay();
        $invoices = Invoice::with('customer')
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->get();

        $buckets = [
            'current' => 0,
            '1_30' => 0,
            '31_60' => 0,
            '61_90' => 0,
            'over_90' => 0,
        ];

        foreach ($invoices as $invoice) {
            $days = $invoice->due_date ? $invoice->due_date->diffInDays($today, false) : 0;
            $amount = $invoice->balance_due;

            if ($days <= 0) {
                $buckets['current'] += $amount;
            } elseif ($days <= 30) {
                $buckets['1_30'] += $amount;
            } elseif ($days <= 60) {
                $buckets['31_60'] += $amount;
            } elseif ($days <= 90) {
                $buckets['61_90'] += $amount;
            } else {
                $buckets['over_90'] += $amount;
            }
        }

        return [
            'invoices' => $invoices,
            'buckets' => $buckets,
        ];
    }

    protected function buildSummaryData(): array
    {
        $this->authorize('viewAny', Invoice::class);

        $rows = Invoice::with('customer')
            ->selectRaw('customer_id, currency, SUM(grand_total) as total, SUM(balance_due) as balance_due')
            ->groupBy('customer_id', 'currency')
            ->orderBy('currency')
            ->get()
            ->map(fn (Invoice $invoice) => [
                'customer' => $invoice->customer?->name ?? __('Bilinmeyen'),
                'currency' => $invoice->currency,
                'total' => (float) $invoice->total,
                'balance_due' => (float) $invoice->balance_due,
                'paid' => max(0.0, (float) $invoice->total - (float) $invoice->balance_due),
            ]);

        $totals = $rows
            ->groupBy('currency')
            ->map(function ($group, $currency) {
                $total = $group->sum('total');
                $paid = $group->sum('paid');
                $outstanding = $group->sum('balance_due');

                return [
                    'currency' => $currency,
                    'total' => $total,
                    'paid' => $paid,
                    'outstanding' => $outstanding,
                ];
            })
            ->sortKeys()
            ->values();

        return [
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    protected function agingCsv($invoices): StreamedResponse
    {
        return Response::streamDownload(function () use ($invoices): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fatura', 'Müşteri', 'Vade', 'Para Birimi', 'Kalan']);
            foreach ($invoices as $invoice) {
                fputcsv($out, [
                    $invoice->invoice_no,
                    $invoice->customer?->name ?? '—',
                    optional($invoice->due_date)->format('Y-m-d'),
                    $invoice->currency,
                    number_format($invoice->balance_due, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, 'aging-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    protected function receiptsCsv($receipts): StreamedResponse
    {
        return Response::streamDownload(function () use ($receipts): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Makbuz', 'Tarih', 'Müşteri', 'Tutar', 'Para Birimi']);
            foreach ($receipts as $receipt) {
                fputcsv($out, [
                    $receipt->receipt_no,
                    optional($receipt->receipt_date)->format('Y-m-d'),
                    $receipt->customer?->name ?? '—',
                    number_format($receipt->amount, 2, '.', ''),
                    $receipt->currency,
                ]);
            }
            fclose($out);
        }, 'receipts-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    protected function summaryCsv($rows): StreamedResponse
    {
        return Response::streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Müşteri', 'Para Birimi', 'Toplam', 'Tahsil Edilen', 'Kalan']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['customer'],
                    $row['currency'],
                    number_format($row['total'], 2, '.', ''),
                    number_format($row['paid'], 2, '.', ''),
                    number_format($row['balance_due'], 2, '.', ''),
                ]);
            }
            fclose($out);
        }, 'ar-summary-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
