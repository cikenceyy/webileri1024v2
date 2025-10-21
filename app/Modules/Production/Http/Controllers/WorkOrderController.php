<?php

namespace App\Modules\Production\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Services\WoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(private readonly WoService $service)
    {
        $this->authorizeResource(WorkOrder::class, 'workOrder');
    }

    public function index(Request $request): View
    {
        $workOrders = WorkOrder::query()
            ->with(['order', 'product'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('production::work_orders.index', [
            'workOrders' => $workOrders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = currentCompanyId();

        if (! $companyId) {
            abort(403, 'Şirket seçimi gerekli.');
        }

        $data = $request->validate([
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where(fn ($q) => $q->where('company_id', $companyId))],
            'order_line_id' => ['nullable', 'integer', Rule::exists('order_lines', 'id')->where(fn ($q) => $q->where('company_id', $companyId))],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')->where(fn ($q) => $q->where('company_id', $companyId))],
            'variant_id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where(fn ($q) => $q->where('company_id', $companyId))],
            'qty' => ['required', 'numeric', 'min:0.001'],
            'unit' => ['nullable', 'string', 'max:32'],
            'planned_start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['company_id'] = $companyId;
        $data['status'] = 'draft';
        $data['work_order_no'] = WorkOrder::generateNo($companyId);
        $data['unit'] = $data['unit'] ?: 'adet';

        WorkOrder::create($data);

        return Redirect::route('admin.production.work-orders.index')
            ->with('status', 'İş emri oluşturuldu.');
    }

    public function show(Request $request, WorkOrder $workOrder): View
    {
        $workOrder->load(['order.customer', 'product', 'variant', 'materialIssues', 'receipts']);

        return view('production::work_orders.show', [
            'workOrder' => $workOrder,
        ]);
    }

    public function update(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $data = $request->validate([
            'planned_start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['draft', 'planned', 'in_progress', 'done', 'cancelled'])],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = Arr::only($data, ['planned_start_date', 'due_date', 'notes']);

        if (isset($data['status'])) {
            $payload['status'] = $data['status'];
        }

        if ($payload !== []) {
            $workOrder->fill($payload)->save();
        }

        return Redirect::route('admin.production.work-orders.show', $workOrder)
            ->with('status', 'İş emri güncellendi.');
    }

    public function close(WorkOrder $workOrder): RedirectResponse
    {
        try {
            Gate::authorize('close', $workOrder);
        } catch (AuthorizationException $exception) {
            return Redirect::route('admin.production.work-orders.show', $workOrder)
                ->withErrors(['status' => $exception->getMessage() ?: __('You are not allowed to close this work order.')]);
        }

        $this->service->close($workOrder);

        return Redirect::route('admin.production.work-orders.show', $workOrder)
            ->with('status', 'İş emri tamamlandı.');
    }
}
