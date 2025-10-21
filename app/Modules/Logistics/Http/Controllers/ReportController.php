<?php

namespace App\Modules\Logistics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const STATUSES = ['draft', 'picking', 'packed', 'shipped', 'delivered', 'returned'];

    public function register(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Shipment::class);

        $query = Shipment::with(['customer', 'order'])
            ->latest('ship_date');

        $filters = [
            'date_from' => $request->date('date_from'),
            'date_to' => $request->date('date_to'),
            'carrier' => $request->get('carrier'),
            'status' => $request->get('status'),
        ];

        if (! empty($filters['status']) && ! in_array($filters['status'], self::STATUSES, true)) {
            $filters['status'] = null;
        }

        if ($filters['date_from']) {
            $query->whereDate('ship_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('ship_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['carrier'])) {
            $query->where('carrier', $filters['carrier']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if ($request->get('format') === 'csv') {
            return $this->exportCsv($query->get());
        }

        if ($request->boolean('print')) {
            return view('logistics::reports.register-print', [
                'shipments' => $query->get(),
                'filters' => $filters,
                'statuses' => $this->statusLabels(),
            ]);
        }

        return view('logistics::reports.register', [
            'shipments' => $query->paginate(25)->withQueryString(),
            'filters' => array_map(fn ($value) => $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value, $filters),
            'statuses' => $this->statusLabels(),
        ]);
    }

    private function statusLabels(): array
    {
        return [
            'draft' => __('Taslak'),
            'picking' => __('Toplanıyor'),
            'packed' => __('Paketlendi'),
            'shipped' => __('Sevk Edildi'),
            'delivered' => __('Teslim Edildi'),
            'returned' => __('İade'),
        ];
    }

    protected function exportCsv($shipments): StreamedResponse
    {
        return Response::streamDownload(function () use ($shipments): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Sevkiyat', 'Tarih', 'Durum', 'Taşıyıcı', 'Müşteri']);
            foreach ($shipments as $shipment) {
                fputcsv($out, [
                    $shipment->shipment_no,
                    optional($shipment->ship_date)->format('Y-m-d'),
                    $shipment->status,
                    $shipment->carrier,
                    $shipment->customer?->name ?? '—',
                ]);
            }
            fclose($out);
        }, 'shipment-register-' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
