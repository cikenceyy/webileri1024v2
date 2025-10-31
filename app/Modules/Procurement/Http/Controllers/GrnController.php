<?php

namespace App\Modules\Procurement\Http\Controllers;

use App\Core\Bus\Events\GrnReceived;
use App\Core\Support\TableKit\Filters;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Domain\Models\Grn;
use App\Modules\Procurement\Domain\Models\GrnLine;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GrnController extends Controller
{
    public function index(Request $request): View
    {
        $query = Grn::query()
            ->with('purchaseOrder')
            ->whereHas('purchaseOrder', fn ($builder) => $builder->where('company_id', currentCompanyId()))
            ->orderByDesc('created_at');

        $search = trim((string) $request->query('q', ''));
        $statusFilters = Filters::multi($request, 'status');
        [$receivedFrom, $receivedTo] = Filters::range($request, 'received_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('id', (int) $search)
                    ->orWhereHas('purchaseOrder', function ($poQuery) use ($search): void {
                        $poQuery->where('po_number', 'like', "%{$search}%");
                    });
            });
        }

        $normalizedStatuses = collect($statusFilters)
            ->filter(fn (string $status) => in_array($status, ['partial', 'received'], true))
            ->values();

        if ($normalizedStatuses->count() === 1) {
            $query->where('status', $normalizedStatuses->first());
        } elseif ($normalizedStatuses->count() > 1) {
            $query->whereIn('status', $normalizedStatuses->all());
        }

        if ($receivedFrom) {
            $query->whereDate('received_at', '>=', $receivedFrom);
        }

        if ($receivedTo) {
            $query->whereDate('received_at', '<=', $receivedTo);
        }

        $perPage = (int) $request->integer('perPage', 25);
        $perPage = max(10, min(100, $perPage));

        return view('procurement::grns.index', [
            'goodsReceipts' => $query->paginate($perPage)->withQueryString(),
            'filters' => [
                'q' => $search,
                'status' => $normalizedStatuses->all(),
                'received_at' => [
                    'from' => $receivedFrom,
                    'to' => $receivedTo,
                ],
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $purchaseOrderId = $request->integer('purchase_order_id');
        $purchaseOrder = null;

        if ($purchaseOrderId) {
            $purchaseOrder = PurchaseOrder::query()
                ->with('lines.grnLines')
                ->findOrFail($purchaseOrderId);
        }

        $availableOrders = PurchaseOrder::query()
            ->whereIn('status', ['approved', 'closed'])
            ->orderByDesc('created_at')
            ->get();

        return view('procurement::grns.create', compact('purchaseOrder', 'availableOrders'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'purchase_order_id' => ['required', 'integer', 'exists:purchase_orders,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.po_line_id' => ['required', 'integer'],
            'lines.*.qty_received' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseOrder = PurchaseOrder::query()
            ->with('lines')
            ->findOrFail($data['purchase_order_id']);

        if ($purchaseOrder->status === 'draft') {
            throw ValidationException::withMessages([
                'purchase_order_id' => 'Taslak siparişler için mal kabulü yapılamaz.',
            ]);
        }

        $filteredLines = collect($data['lines'])
            ->filter(fn (array $line): bool => (float) $line['qty_received'] > 0)
            ->values();

        if ($filteredLines->isEmpty()) {
            throw ValidationException::withMessages([
                'lines' => 'En az bir satır için kabul miktarı girilmelidir.',
            ]);
        }

        $grn = DB::transaction(function () use ($purchaseOrder, $filteredLines): Grn {
            $grn = Grn::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'status' => 'partial',
                'received_at' => now(),
            ]);

            foreach ($filteredLines as $index => $lineData) {
                $poLine = $purchaseOrder->lines->firstWhere('id', $lineData['po_line_id']);

                if (! $poLine) {
                    throw ValidationException::withMessages([
                        "lines.$index.po_line_id" => 'Satır satınalma siparişine ait değil.',
                    ]);
                }

                $alreadyReceived = GrnLine::query()
                    ->where('po_line_id', $poLine->id)
                    ->sum('qty_received');

                $remaining = (float) $poLine->qty_ordered - (float) $alreadyReceived;
                $qtyReceived = (float) $lineData['qty_received'];

                if ($qtyReceived > $remaining + 1e-6) {
                    throw ValidationException::withMessages([
                        "lines.$index.qty_received" => 'Kabul miktarı sipariş miktarını aşamaz.',
                    ]);
                }

                $grn->lines()->create([
                    'po_line_id' => $poLine->id,
                    'product_id' => $poLine->product_id,
                    'qty_received' => $lineData['qty_received'],
                ]);
            }

            $allReceived = true;

            foreach ($purchaseOrder->lines as $poLine) {
                $ordered = (float) $poLine->qty_ordered;
                $received = GrnLine::query()
                    ->where('po_line_id', $poLine->id)
                    ->sum('qty_received');

                if ($received + 1e-6 < $ordered) {
                    $allReceived = false;
                    break;
                }
            }

            $grn->update([
                'status' => $allReceived ? 'received' : 'partial',
            ]);

            if ($allReceived) {
                $purchaseOrder->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);
            }

            return $grn;
        });

        $grn->load(['purchaseOrder.lines', 'lines.poLine']);

        event(new GrnReceived($grn));

        return redirect()
            ->route('admin.procurement.grns.show', $grn)
            ->with('status', 'Mal kabulü kaydedildi.');
    }

    public function show(Grn $grn): View
    {
        $grn->load(['purchaseOrder.lines', 'lines.poLine']);

        return view('procurement::grns.show', compact('grn'));
    }
}
