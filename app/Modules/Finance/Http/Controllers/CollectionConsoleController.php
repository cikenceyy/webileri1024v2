<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class CollectionConsoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::query()
            ->with('customer:id,name')
            ->whereIn('status', ['published', 'partial'])
            ->where('balance_due', '>', 0);

        $search = (string) $request->string('q')->trim();
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $like = '%' . $search . '%';
                $q->where('invoice_no', 'like', $like)
                    ->orWhereHas('customer', static function ($customer) use ($like): void {
                        $customer->where('name', 'like', $like);
                    });
            });
        }

        $invoices = $query
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->get(['id', 'invoice_no', 'customer_id', 'due_date', 'balance_due', 'currency', 'collection_lane']);

        $lanes = $this->prepareLanes($invoices);

        return view('finance::collections.index', [
            'laneDefinitions' => $this->laneDefinitions(),
            'lanes' => $lanes,
            'filters' => $request->only('q'),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['customer', 'allocations.receipt']);

        $receipts = Receipt::query()
            ->where('customer_id', $invoice->customer_id)
            ->whereColumn('amount', '>', 'allocated_total')
            ->latest('receipt_date')
            ->limit(10)
            ->get(['id', 'receipt_no', 'amount', 'allocated_total', 'currency', 'receipt_date']);

        return view('finance::collections.summary', [
            'receipts' => $receipts,
            'invoice' => $invoice,
        ]);
    }

    public function updateLane(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $lane = $request->string('lane')->toString();

        $validatedLane = $request->validate([
            'lane' => ['required', Rule::in(array_keys($this->laneDefinitions()))],
        ])['lane'];

        $invoice->forceFill([
            'collection_lane' => $validatedLane,
        ])->save();

        return response()->json([
            'status' => 'ok',
            'lane' => $validatedLane,
        ]);
    }

    protected function prepareLanes(Collection $invoices): array
    {
        $definitions = $this->laneDefinitions();
        $today = Carbon::today();
        $endOfWeek = Carbon::today()->endOfWeek();

        $lanes = [];

        foreach ($definitions as $key => $definition) {
            $lanes[$key] = [
                'meta' => $definition,
                'items' => [],
            ];
        }

        foreach ($invoices as $invoice) {
            $laneKey = $invoice->collection_lane ?: $this->autoLane($invoice, $today, $endOfWeek);

            if (! array_key_exists($laneKey, $lanes)) {
                $laneKey = 'follow_up';
            }

            $lanes[$laneKey]['items'][] = [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer' => $invoice->customer?->name ?? '—',
                'due_date' => $invoice->due_date,
                'balance_due' => $invoice->balance_due,
                'currency' => $invoice->currency,
                'lane' => $laneKey,
                'due_state' => $this->dueState($invoice->due_date, $today),
            ];
        }

        foreach ($lanes as $key => $lane) {
            $lanes[$key]['meta']['count'] = count($lane['items']);
            $lanes[$key]['meta']['total'] = collect($lane['items'])->sum('balance_due');
        }

        return $lanes;
    }

    protected function laneDefinitions(): array
    {
        return [
            'today' => [
                'label' => __('Bugün'),
                'description' => __('Vadesi bugün dolan tahsilatlar'),
            ],
            'week' => [
                'label' => __('Bu Hafta'),
                'description' => __('7 gün içinde vadesi yaklaşanlar'),
            ],
            'overdue' => [
                'label' => __('Gecikmiş'),
                'description' => __('Vadesi geçmiş ve bakiye bekleyenler'),
            ],
            'follow_up' => [
                'label' => __('Takipte'),
                'description' => __('Planlanan hatırlatma veya özel durumlar'),
            ],
        ];
    }

    protected function autoLane(Invoice $invoice, Carbon $today, Carbon $endOfWeek): string
    {
        if (! $invoice->due_date) {
            return 'follow_up';
        }

        if ($invoice->due_date->isSameDay($today)) {
            return 'today';
        }

        if ($invoice->due_date->lessThan($today)) {
            return 'overdue';
        }

        if ($invoice->due_date->lessThanOrEqualTo($endOfWeek)) {
            return 'week';
        }

        return 'follow_up';
    }

    protected function dueState(?Carbon $dueDate, Carbon $today): array
    {
        if (! $dueDate) {
            return [
                'label' => __('Vade yok'),
                'variant' => 'secondary',
            ];
        }

        if ($dueDate->isSameDay($today)) {
            return [
                'label' => __('Bugün'),
                'variant' => 'warning',
            ];
        }

        if ($dueDate->lessThan($today)) {
            return [
                'label' => __('%d gün gecikmiş', $dueDate->diffInDays($today)),
                'variant' => 'danger',
            ];
        }

        return [
            'label' => __('%d gün kaldı', $today->diffInDays($dueDate)),
            'variant' => 'info',
        ];
    }
}
