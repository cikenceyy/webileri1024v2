<?php

namespace App\Modules\Procurement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Procurement\Domain\Models\PoLine;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PoController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PurchaseOrder::class, 'po');
    }

    public function index(): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->withCount('lines')
            ->latest()
            ->paginate(15);

        return view('procurement::pos.index', compact('purchaseOrders'));
    }

    public function create(): View
    {
        return view('procurement::pos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'currency' => ['required', 'string', 'size:3'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['nullable', 'integer'],
            'lines.*.description' => ['required', 'string', 'max:255'],
            'lines.*.qty_ordered' => ['required', 'numeric', 'min:0.001'],
            'lines.*.unit' => ['required', 'string', 'max:20'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $purchaseOrder = DB::transaction(function () use ($data): PurchaseOrder {
            $companyId = currentCompanyId();

            if (! $companyId) {
                abort(403, 'Şirket seçimi gerekli.');
            }

            $purchaseOrder = PurchaseOrder::query()->create([
                'company_id' => $companyId,
                'supplier_id' => $data['supplier_id'],
                'po_number' => PurchaseOrder::generateNumber($companyId),
                'status' => 'draft',
                'currency' => strtoupper($data['currency']),
                'total' => 0,
            ]);

            $total = 0;

            foreach ($data['lines'] as $lineData) {
                $lineTotal = round((float) $lineData['qty_ordered'] * (float) $lineData['unit_price'], 2);

                PoLine::query()->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $lineData['product_id'] ?? null,
                    'description' => $lineData['description'],
                    'qty_ordered' => $lineData['qty_ordered'],
                    'unit' => $lineData['unit'],
                    'unit_price' => $lineData['unit_price'],
                    'line_total' => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $purchaseOrder->update(['total' => $total]);

            return $purchaseOrder;
        });

        return redirect()
            ->route('admin.procurement.pos.show', $purchaseOrder)
            ->with('status', 'Satınalma siparişi oluşturuldu.');
    }

    public function show(PurchaseOrder $po): View
    {
        $po->load(['lines.grnLines', 'goodsReceipts']);

        return view('procurement::pos.show', ['purchaseOrder' => $po]);
    }

    public function update(Request $request, PurchaseOrder $po): RedirectResponse
    {
        $data = Validator::make(
            $request->all(),
            [
                'status' => ['required', Rule::in(['draft', 'approved', 'closed'])],
            ]
        )->validate();

        $status = $data['status'];

        if ($status === 'draft' && $po->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Onaylanmış siparişler taslağa alınamaz.',
            ]);
        }

        if ($status === 'approved' && $po->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Sadece taslak siparişler onaylanabilir.',
            ]);
        }

        if ($status === 'approved' && $po->status === 'draft') {
            try {
                Gate::authorize('approve', $po);
            } catch (AuthorizationException $exception) {
                throw ValidationException::withMessages([
                    'status' => $exception->getMessage() ?: 'Bu satınalma siparişini onaylama yetkiniz yok.',
                ]);
            }
        }

        if ($status === 'closed' && ! in_array($po->status, ['approved', 'closed'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Sipariş kapanmadan önce onaylanmalıdır.',
            ]);
        }

        $attributes = ['status' => $status];

        if ($status === 'approved') {
            $attributes['approved_at'] = now();
        }

        if ($status === 'closed') {
            $attributes['closed_at'] = now();
        }

        $po->update($attributes);

        return redirect()
            ->route('admin.procurement.pos.show', $po)
            ->with('status', 'Sipariş durumu güncellendi.');
    }
}
