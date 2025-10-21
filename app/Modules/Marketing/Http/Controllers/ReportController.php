<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Marketing\Domain\Models\OrderLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $data = $this->buildSalesData($request);

        return view('marketing::reports.sales', $data);
    }

    public function salesPrint(Request $request): View
    {
        $data = $this->buildSalesData($request);

        return view('marketing::reports.sales-print', $data);
    }

    public function salesExport(Request $request): StreamedResponse|RedirectResponse
    {
        $type = $request->get('type', 'customer');
        $data = $this->buildSalesData($request);

        $rows = match ($type) {
            'product' => $data['productRows'],
            default => $data['customerRows'],
        };

        $filename = 'sales-' . $type . '-' . now()->format('Ymd_His') . '.csv';

        return Response::streamDownload(function () use ($rows, $type): void {
            $output = fopen('php://output', 'w');
            if ($type === 'product') {
                fputcsv($output, ['Ürün', 'Para Birimi', 'Satış Adedi', 'Satış Tutarı']);
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row['label'],
                        $row['currency'],
                        number_format($row['quantity'], 3, '.', ''),
                        number_format($row['amount'], 2, '.', ''),
                    ]);
                }
            } else {
                fputcsv($output, ['Müşteri', 'Para Birimi', 'Sipariş Sayısı', 'Satış Tutarı']);
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row['label'],
                        $row['currency'],
                        $row['orders'],
                        number_format($row['amount'], 2, '.', ''),
                    ]);
                }
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function buildSalesData(Request $request): array
    {
        $this->authorize('viewAny', Order::class);

        $filters = [
            'date_from' => $request->date('date_from'),
            'date_to' => $request->date('date_to'),
            'status' => $request->get('status'),
        ];

        $orderQuery = Order::with('customer');
        $this->applyOrderFilters($orderQuery, $filters);
        $orders = $orderQuery->get();

        $customerRows = $orders
            ->groupBy(fn (Order $order) => ($order->customer?->name ?? __('Bilinmeyen')) . '|' . $order->currency)
            ->map(function (Collection $group) {
                /** @var Order $sample */
                $sample = $group->first();

                return [
                    'label' => $sample->customer?->name ?? __('Bilinmeyen'),
                    'currency' => $sample->currency,
                    'orders' => $group->count(),
                    'amount' => $group->sum('total_amount'),
                ];
            })
            ->values();

        $productRows = $this->buildProductRows($filters);

        return [
            'customerRows' => $customerRows,
            'productRows' => $productRows,
            'filters' => [
                'date_from' => optional($filters['date_from'])->format('Y-m-d'),
                'date_to' => optional($filters['date_to'])->format('Y-m-d'),
                'status' => $filters['status'],
            ],
        ];
    }

    private function buildProductRows(array $filters): Collection
    {
        $lineQuery = OrderLine::query()
            ->selectRaw('order_lines.description as label, orders.currency as currency, SUM(order_lines.qty) as qty_total, SUM(order_lines.line_total) as amount_total')
            ->join('orders', 'orders.id', '=', 'order_lines.order_id');

        $this->applyOrderFilters($lineQuery, $filters, 'orders.');

        return $lineQuery
            ->groupBy('order_lines.description', 'orders.currency')
            ->orderByDesc('amount_total')
            ->limit(50)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label ?: __('Tanımsız'),
                'currency' => $row->currency,
                'quantity' => (float) $row->qty_total,
                'amount' => (float) $row->amount_total,
            ]);
    }

    private function applyOrderFilters($query, array $filters, string $prefix = ''): void
    {
        if ($filters['date_from']) {
            $query->whereDate($prefix . 'order_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate($prefix . 'order_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['status']) && in_array($filters['status'], ['draft', 'confirmed', 'shipped', 'cancelled'], true)) {
            $query->where($prefix . 'status', $filters['status']);
        }
    }
}
