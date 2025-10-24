<?php

namespace App\Modules\Logistics\Http\Controllers\Admin;

use App\Core\Contracts\SettingsReader;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Models\WarehouseBin;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\GoodsReceiptLine;
use App\Modules\Logistics\Domain\Services\LogisticsSequencer;
use App\Modules\Logistics\Domain\Services\ReceiptPoster;
use App\Modules\Logistics\Domain\Services\ReceiptReconciler;
use App\Modules\Logistics\Http\Requests\Admin\ReceiptReceiveRequest;
use App\Modules\Logistics\Http\Requests\Admin\ReceiptReconcileRequest;
use App\Modules\Logistics\Http\Requests\Admin\ReceiptStoreRequest;
use App\Modules\Logistics\Http\Requests\Admin\ReceiptUpdateRequest;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use LogicException;

class ReceiptController extends Controller
{
    public function __construct(
        private readonly LogisticsSequencer $sequencer,
        private readonly ReceiptPoster $poster,
        private readonly ReceiptReconciler $reconciler,
        private readonly SettingsReader $settingsReader,
        private readonly Dispatcher $events,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GoodsReceipt::class);

        $companyId = currentCompanyId();
        $status = $request->string('status')->toString();

        $receipts = GoodsReceipt::query()
            ->where('company_id', $companyId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('logistics::admin.receipts.index', [
            'receipts' => $receipts,
            'filters' => ['status' => $status],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', GoodsReceipt::class);

        $companyId = currentCompanyId();
        $settings = $this->settingsReader->get($companyId);
        $defaults = [
            'warehouse_id' => Arr::get($settings->defaults, 'receipt_warehouse_id'),
        ];

        return view('logistics::admin.receipts.create', [
            'products' => Product::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'bins' => WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get(),
            'defaults' => $defaults,
        ]);
    }

    public function store(ReceiptStoreRequest $request): RedirectResponse
    {
        $companyId = currentCompanyId();
        $data = $request->validated();

        $receipt = DB::transaction(function () use ($companyId, $data) {
            $docNo = $this->sequencer->nextReceipt($companyId);

            $receipt = GoodsReceipt::create([
                'company_id' => $companyId,
                'doc_no' => $docNo,
                'vendor_id' => $data['vendor_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $index => $line) {
                GoodsReceiptLine::create([
                    'company_id' => $companyId,
                    'receipt_id' => $receipt->id,
                    'product_id' => $line['product_id'],
                    'variant_id' => $line['variant_id'] ?? null,
                    'qty_expected' => $line['qty_expected'] ?? null,
                    'notes' => $line['notes'] ?? null,
                    'sort' => $index,
                ]);
            }

            return $receipt;
        });

        return redirect()->route('admin.logistics.receipts.show', $receipt)
            ->with('status', __('Goods receipt created.'));
    }

    public function show(GoodsReceipt $receipt): View
    {
        $this->authorize('view', $receipt);

        $receipt->load(['lines.product', 'lines.variant', 'lines.warehouse', 'lines.bin']);

        $companyId = currentCompanyId();
        $settings = $this->settingsReader->get($companyId);

        return view('logistics::admin.receipts.show', [
            'receipt' => $receipt,
            'products' => Product::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'bins' => WarehouseBin::query()->where('company_id', $companyId)->orderBy('code')->get(),
            'defaults' => [
                'warehouse_id' => Arr::get($settings->defaults, 'receipt_warehouse_id'),
            ],
        ]);
    }

    public function edit(GoodsReceipt $receipt): View
    {
        $this->authorize('update', $receipt);

        if (in_array($receipt->status, ['received', 'reconciled', 'closed', 'cancelled'], true)) {
            throw new LogicException('Received goods receipts cannot be edited.');
        }

        $receipt->load(['lines.product', 'lines.variant']);
        $companyId = currentCompanyId();

        return view('logistics::admin.receipts.edit', [
            'receipt' => $receipt,
            'products' => Product::query()->where('company_id', $companyId)->orderBy('name')->get(),
        ]);
    }

    public function update(ReceiptUpdateRequest $request, GoodsReceipt $receipt): RedirectResponse
    {
        if (in_array($receipt->status, ['received', 'reconciled', 'closed', 'cancelled'], true)) {
            throw new LogicException('Received goods receipts cannot be edited.');
        }

        $data = $request->validated();
        $companyId = currentCompanyId();

        DB::transaction(function () use ($receipt, $data, $companyId) {
            $receipt->update([
                'vendor_id' => $data['vendor_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $lineIds = [];
            foreach ($data['lines'] as $index => $lineData) {
                $lineId = $lineData['id'] ?? null;
                $payload = [
                    'company_id' => $companyId,
                    'product_id' => $lineData['product_id'],
                    'variant_id' => $lineData['variant_id'] ?? null,
                    'qty_expected' => $lineData['qty_expected'] ?? null,
                    'notes' => $lineData['notes'] ?? null,
                    'sort' => $index,
                ];

                if ($lineId) {
                    $line = GoodsReceiptLine::query()
                        ->where('company_id', $companyId)
                        ->where('receipt_id', $receipt->id)
                        ->findOrFail($lineId);
                    $line->update($payload);
                    $lineIds[] = $line->id;
                } else {
                    $newLine = GoodsReceiptLine::create(array_merge($payload, [
                        'receipt_id' => $receipt->id,
                    ]));
                    $lineIds[] = $newLine->id;
                }
            }

            GoodsReceiptLine::query()
                ->where('company_id', $companyId)
                ->where('receipt_id', $receipt->id)
                ->whereNotIn('id', $lineIds)
                ->delete();
        });

        return redirect()->route('admin.logistics.receipts.show', $receipt)
            ->with('status', __('Goods receipt updated.'));
    }

    public function receive(ReceiptReceiveRequest $request, GoodsReceipt $receipt): RedirectResponse
    {
        $data = $request->validated();

        $linePayloads = collect($data['lines'])
            ->mapWithKeys(fn ($line) => [$line['id'] => [
                'qty_expected' => $line['qty_expected'] ?? null,
                'qty_received' => $line['qty_received'],
                'warehouse_id' => $line['warehouse_id'] ?? null,
                'bin_id' => $line['bin_id'] ?? null,
                'variance_reason' => $line['variance_reason'] ?? null,
            ]])->all();

        $this->poster->receive($receipt, $linePayloads, $data['warehouse_id'] ?? null);

        $this->events->dispatch(new \App\Modules\Logistics\Domain\Events\GoodsReceiptPosted($receipt->fresh('lines')));

        return back()->with('status', __('Goods receipt posted.'));
    }

    public function reconcile(ReceiptReconcileRequest $request, GoodsReceipt $receipt): RedirectResponse
    {
        $payloads = collect($request->validated('lines'))
            ->mapWithKeys(fn ($line) => [$line['id'] => [
                'variance_reason' => $line['variance_reason'] ?? null,
                'notes' => $line['notes'] ?? null,
            ]])->all();

        $this->reconciler->reconcile($receipt, $payloads);

        return back()->with('status', __('Variance reconciled.'));
    }

    public function close(GoodsReceipt $receipt): RedirectResponse
    {
        $this->authorize('close', $receipt);

        if (! in_array($receipt->status, ['received', 'reconciled'], true)) {
            throw new LogicException('Only received receipts can be closed.');
        }

        $receipt->update(['status' => 'closed']);

        return back()->with('status', __('Goods receipt closed.'));
    }

    public function cancel(GoodsReceipt $receipt): RedirectResponse
    {
        $this->authorize('cancel', $receipt);

        if (in_array($receipt->status, ['reconciled', 'closed'], true)) {
            throw new LogicException('Completed receipts cannot be cancelled.');
        }

        $receipt->update(['status' => 'cancelled']);

        return back()->with('status', __('Goods receipt cancelled.'));
    }

    public function print(GoodsReceipt $receipt): View
    {
        $this->authorize('print', $receipt);

        $receipt->load(['lines.product', 'lines.variant']);
        $settings = $this->settingsReader->get(currentCompanyId());
        $template = Arr::get($settings->documents, 'grn_note_template');

        if ($template && view()->exists($template)) {
            return view($template, ['receipt' => $receipt]);
        }

        return view('logistics::admin.receipts.print', [
            'receipt' => $receipt,
        ]);
    }
}
