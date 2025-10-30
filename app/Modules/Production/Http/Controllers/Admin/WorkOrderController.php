<?php

namespace App\Modules\Production\Http\Controllers\Admin;

use App\Core\Contracts\SettingsReader;
use App\Core\Support\TableKit\TableConfig;
use App\Core\TableKit\QueryAdapter;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\WorkOrder;
use App\Modules\Production\Domain\Services\BomExpander;
use App\Modules\Production\Domain\Services\WorkOrderCompleter;
use App\Modules\Production\Domain\Services\WorkOrderIssuer;
use App\Modules\Production\Domain\Services\WorkOrderSequencer;
use App\Modules\Production\Http\Requests\Admin\WorkOrderCompleteRequest;
use App\Modules\Production\Http\Requests\Admin\WorkOrderIssueRequest;
use App\Modules\Production\Http\Requests\Admin\WorkOrderStoreRequest;
use App\Modules\Production\Http\Requests\Admin\WorkOrderUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(
        private readonly WorkOrderSequencer $sequencer,
        private readonly BomExpander $bomExpander,
        private readonly WorkOrderIssuer $issuer,
        private readonly WorkOrderCompleter $completer,
        private readonly SettingsReader $settingsReader,
    ) {
    }

    /**
     * İş emri listesini TableKit üzerinden düşük maliyetle sunar.
     * Maliyet Notu: Tek sorgu + cursor pagination ile 1 dk sıcak cache kullanıyoruz.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', WorkOrder::class);
        $companyId = currentCompanyId();

        $builder = WorkOrder::query()
            ->where('company_id', $companyId)
            ->with(['product:id,company_id,name']);

        $adapter = QueryAdapter::make($builder, 'production:workorders')
            ->select([
                'id',
                'company_id',
                'product_id',
                'number',
                'planned_qty',
                'status',
                'due_date',
                'created_at',
            ])
            ->allowSorts(['number', 'planned_qty', 'status', 'due_date', 'created_at'])
            ->allowFilters(['status'])
            ->defaultSort('-created_at')
            ->mapUsing(static function (WorkOrder $order): array {
                $number = $order->number ?: sprintf('WO-%05d', $order->id);

                return [
                    'id' => $order->id,
                    'number' => $number,
                    'product' => $order->product->name ?? '—',
                    'planned_qty' => number_format((float) ($order->planned_qty ?? 0), 0, ',', '.'),
                    'status' => $order->status ?? 'draft',
                    'due_date' => optional($order->due_date)?->format('Y-m-d') ?? '—',
                ];
            });

        $payload = $adapter->toPayload($request);

        $tableKitConfig = TableConfig::make([
            ['key' => 'number', 'label' => __('İş Emri #'), 'sortable' => true, 'width' => '140px'],
            ['key' => 'product', 'label' => __('Ürün')],
            ['key' => 'planned_qty', 'label' => __('Planlanan'), 'sortable' => true, 'class' => 'text-end', 'width' => '120px'],
            ['key' => 'status', 'label' => __('Durum'), 'sortable' => true, 'width' => '120px'],
            ['key' => 'due_date', 'label' => __('Termin'), 'sortable' => true, 'width' => '140px'],
        ], [
            'id' => 'workorders',
            'default_sort' => $payload['meta']['default_sort'],
            'data_count' => $payload['paginator']->count(),
            'row_actions' => [
                ['type' => 'link', 'label' => __('Aç'), 'route' => 'admin.production.workorders.show', 'param' => 'id'],
                ['type' => 'link', 'label' => __('Düzenle'), 'route' => 'admin.production.workorders.edit', 'param' => 'id'],
            ],
        ]);

        $tableKitRows = $payload['rows'];
        $tableKitPaginator = $payload['paginator'];

        return view('production::admin.workorders.index', compact('tableKitConfig', 'tableKitRows', 'tableKitPaginator'));
    }


    public function create(): View
    {
        $this->authorize('create', WorkOrder::class);

        $companyId = currentCompanyId();
        $products = Product::query()->where('company_id', $companyId)->orderBy('name')->get();
        $boms = Bom::query()->where('company_id', $companyId)->orderBy('code')->get();
        $warehouses = Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get();

        $settings = $this->settingsReader->get($companyId);
        $defaults = $settings->defaults;

        return view('production::admin.workorders.create', [
            'products' => $products,
            'boms' => $boms,
            'warehouses' => $warehouses,
            'defaults' => $defaults,
        ]);
    }

    public function store(WorkOrderStoreRequest $request): RedirectResponse
    {
        $companyId = currentCompanyId();
        $data = $request->validated();

        $workOrder = DB::transaction(function () use ($companyId, $data) {
            $docNo = $this->sequencer->next($companyId);

            $workOrder = WorkOrder::create([
                'company_id' => $companyId,
                'doc_no' => $docNo,
                'product_id' => $data['product_id'],
                'variant_id' => $data['variant_id'] ?? null,
                'bom_id' => $data['bom_id'],
                'target_qty' => $data['target_qty'],
                'uom' => $data['uom'] ?: 'pcs',
                'status' => 'draft',
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
            ]);

            return $workOrder;
        });

        return redirect()->route('admin.production.workorders.show', $workOrder)->with('status', __('İş emri oluşturuldu.'));
    }

    public function show(WorkOrder $workOrder): View
    {
        $this->authorize('view', $workOrder);

        $workOrder->load(['product', 'variant', 'bom.items.component', 'issues.component', 'issues.warehouse', 'receipts.warehouse']);

        $companyId = currentCompanyId();
        $settings = $this->settingsReader->get($companyId);
        $precision = (int) Arr::get($settings->general, 'decimal_precision', 2);
        $requirements = $this->bomExpander->expand($workOrder->bom, $workOrder->target_qty, $precision);

        $warehouses = Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get();
        $bins = WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get();

        return view('production::admin.workorders.show', [
            'workOrder' => $workOrder,
            'requirements' => $requirements,
            'warehouses' => $warehouses,
            'bins' => $bins,
            'settingsDefaults' => $settings->defaults,
        ]);
    }

    public function edit(WorkOrder $workOrder): View
    {
        $this->authorize('update', $workOrder);

        if (in_array($workOrder->status, ['completed', 'closed'], true)) {
            abort(403, __('Tamamlanan iş emirleri düzenlenemez.'));
        }

        $companyId = currentCompanyId();
        $products = Product::query()->where('company_id', $companyId)->orderBy('name')->get();
        $boms = Bom::query()->where('company_id', $companyId)->orderBy('code')->get();

        return view('production::admin.workorders.edit', [
            'workOrder' => $workOrder,
            'products' => $products,
            'boms' => $boms,
        ]);
    }

    public function update(WorkOrderUpdateRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        if (in_array($workOrder->status, ['completed', 'closed'], true)) {
            return back()->withErrors(__('Tamamlanan iş emirleri güncellenemez.'));
        }

        $data = $request->validated();
        $workOrder->fill([
            'product_id' => $data['product_id'],
            'variant_id' => $data['variant_id'] ?? null,
            'bom_id' => $data['bom_id'],
            'target_qty' => $data['target_qty'],
            'uom' => $data['uom'] ?: $workOrder->uom,
            'due_date' => $data['due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ])->save();

        return redirect()->route('admin.production.workorders.show', $workOrder)->with('status', __('İş emri güncellendi.'));
    }

    public function release(WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('release', $workOrder);

        if (! in_array($workOrder->status, ['draft', 'cancelled'], true)) {
            return back()->withErrors(__('Yalnızca taslak iş emirleri serbest bırakılabilir.'));
        }

        $workOrder->forceFill([
            'status' => 'released',
        ])->save();

        return back()->with('status', __('İş emri serbest bırakıldı.'));
    }

    public function start(WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('start', $workOrder);

        if (! in_array($workOrder->status, ['released', 'draft'], true)) {
            return back()->withErrors(__('İş emri zaten üretimde.'));
        }

        $workOrder->forceFill([
            'status' => 'in_progress',
            'started_at' => $workOrder->started_at ?? now(),
        ])->save();

        return back()->with('status', __('İş emri üretimde.'));
    }

    public function issue(WorkOrderIssueRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $lines = $request->validated()['lines'];
        $userId = $request->user()->id;

        try {
            $this->issuer->post($workOrder, $lines, $userId);
        } catch (\Throwable $exception) {
            return back()->withErrors($exception->getMessage());
        }

        return redirect()->route('admin.production.workorders.show', $workOrder)->with('status', __('Malzemeler çıkıldı.'));
    }

    public function complete(WorkOrderCompleteRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $payload = $request->validated();
        $userId = $request->user()->id;

        try {
            $this->completer->post($workOrder, $payload, $userId);
        } catch (\Throwable $exception) {
            return back()->withErrors($exception->getMessage());
        }

        return redirect()->route('admin.production.workorders.show', $workOrder)->with('status', __('İş emri tamamlandı.'));
    }

    public function close(WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('close', $workOrder);

        if (! in_array($workOrder->status, ['completed', 'in_progress'], true)) {
            return back()->withErrors(__('Yalnızca tamamlanan iş emirleri kapatılabilir.'));
        }

        $workOrder->forceFill([
            'status' => 'closed',
        ])->save();

        return back()->with('status', __('İş emri kapatıldı.'));
    }

    public function cancel(WorkOrder $workOrder): RedirectResponse
    {
        Gate::authorize('cancel', $workOrder);

        if (in_array($workOrder->status, ['completed', 'closed'], true)) {
            return back()->withErrors(__('Tamamlanan iş emirleri iptal edilemez.'));
        }

        $workOrder->forceFill([
            'status' => 'cancelled',
        ])->save();

        return back()->with('status', __('İş emri iptal edildi.'));
    }
}
