<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Core\Contracts\SettingsReader;
use App\Core\Support\TableKit\Filters;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\StockTransfer;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Domain\Services\InventorySequencer;
use App\Modules\Inventory\Http\Requests\SaveStockTransferRequest;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(
        protected SettingsReader $settingsReader,
        protected DatabaseManager $database,
        protected InventorySequencer $sequencer,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StockTransfer::class);

        $companyId = Auth::user()->company_id;
        $statusFilters = Filters::multi($request, 'status');
        $docNoFilter = Filters::scalar($request, 'doc_no');
        $fromFilter = Filters::scalar($request, 'from');
        $toFilter = Filters::scalar($request, 'to');
        [$createdFrom, $createdTo] = Filters::range($request, 'created_at');

        $allowedStatuses = ['draft', 'posted'];
        $normalizedStatuses = collect($statusFilters)
            ->filter(fn ($value) => in_array($value, $allowedStatuses, true))
            ->values();

        $transfers = StockTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse'])
            ->where('company_id', $companyId)
            ->when(
                $normalizedStatuses->isNotEmpty(),
                fn ($query) => $query->whereIn('status', $normalizedStatuses->all())
            )
            ->when(
                $normalizedStatuses->isEmpty() && $request->filled('status'),
                function ($query) use ($request, $allowedStatuses, &$statusFilters, $normalizedStatuses): void {
                    $legacy = $request->string('status')->trim()->value();
                    if ($legacy !== '' && in_array($legacy, $allowedStatuses, true)) {
                        $query->where('status', $legacy);
                        $statusFilters = [$legacy];
                        $normalizedStatuses->push($legacy);
                    }
                }
            )
            ->when($docNoFilter !== null, fn ($query) => $query->where('doc_no', 'like', "%{$docNoFilter}%"))
            ->when($fromFilter !== null, function ($query) use ($fromFilter): void {
                $query->whereHas('fromWarehouse', function ($warehouseQuery) use ($fromFilter): void {
                    $warehouseQuery->where('name', 'like', "%{$fromFilter}%");
                });
            })
            ->when($toFilter !== null, function ($query) use ($toFilter): void {
                $query->whereHas('toWarehouse', function ($warehouseQuery) use ($toFilter): void {
                    $warehouseQuery->where('name', 'like', "%{$toFilter}%");
                });
            })
            ->when($createdFrom !== null, fn ($query) => $query->whereDate('created_at', '>=', $createdFrom))
            ->when($createdTo !== null, fn ($query) => $query->whereDate('created_at', '<=', $createdTo))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('inventory::transfers.index', [
            'transfers' => $transfers,
            'filters' => [
                'status' => $normalizedStatuses->isNotEmpty() ? $normalizedStatuses->all() : $statusFilters,
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StockTransfer::class);

        $companyId = Auth::user()->company_id;
        $settings = $this->settingsReader->get($companyId);
        $defaults = $settings->defaults;
        $nextDoc = $this->sequencer->nextTransferNumber($companyId);

        $warehouses = Warehouse::query()
            ->with('bins')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('inventory::transfers.create', [
            'warehouses' => $warehouses,
            'defaultWarehouse' => $defaults['warehouse_id'] ?? null,
            'nextDoc' => $nextDoc,
        ]);
    }

    public function store(SaveStockTransferRequest $request): RedirectResponse
    {
        $this->authorize('create', StockTransfer::class);

        $companyId = Auth::user()->company_id;

        $transfer = new StockTransfer([
            'company_id' => $companyId,
            'doc_no' => $request->input('doc_no') ?: $this->sequencer->nextTransferNumber($companyId),
            'from_warehouse_id' => $request->integer('from_warehouse_id'),
            'from_bin_id' => $request->input('from_bin_id'),
            'to_warehouse_id' => $request->integer('to_warehouse_id'),
            'to_bin_id' => $request->input('to_bin_id'),
            'status' => 'draft',
        ]);

        $transfer->save();

        foreach ($request->input('lines', []) as $lineData) {
            $transfer->lines()->create([
                'company_id' => $companyId,
                'product_id' => $lineData['product_id'],
                'qty' => $lineData['qty'],
                'note' => $lineData['note'] ?? null,
            ]);
        }

        return redirect()
            ->route('admin.inventory.transfers.show', $transfer)
            ->with('status', 'Transfer taslağı oluşturuldu');
    }

    public function show(StockTransfer $transfer): View
    {
        $this->authorize('view', $transfer);

        $transfer->load(['lines.product', 'fromWarehouse', 'toWarehouse', 'fromBin', 'toBin']);

        return view('inventory::transfers.show', [
            'transfer' => $transfer,
        ]);
    }

    public function post(StockTransfer $transfer): RedirectResponse
    {
        $this->authorize('post', $transfer);

        if ($transfer->status === 'posted') {
            return back()->withErrors('Transfer zaten gönderildi.');
        }

        $companyId = Auth::user()->company_id;
        $transfer->loadMissing('lines');

        $this->database->transaction(function () use ($transfer, $companyId) {
            $now = Carbon::now();
            $userId = Auth::id();

            foreach ($transfer->lines as $line) {
                StockLedgerEntry::create([
                    'company_id' => $companyId,
                    'product_id' => $line->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'bin_id' => $transfer->from_bin_id,
                    'qty_in' => 0,
                    'qty_out' => $line->qty,
                    'reason' => 'transfer',
                    'ref_type' => StockTransfer::class,
                    'ref_id' => $transfer->id,
                    'doc_no' => $transfer->doc_no,
                    'dated_at' => $now,
                ]);

                StockLedgerEntry::create([
                    'company_id' => $companyId,
                    'product_id' => $line->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'bin_id' => $transfer->to_bin_id,
                    'qty_in' => $line->qty,
                    'qty_out' => 0,
                    'reason' => 'transfer',
                    'ref_type' => StockTransfer::class,
                    'ref_id' => $transfer->id,
                    'doc_no' => $transfer->doc_no,
                    'dated_at' => $now,
                ]);
            }

            $transfer->forceFill([
                'status' => 'posted',
                'posted_at' => $now,
                'posted_by' => $userId,
            ])->save();
        });

        return redirect()
            ->route('admin.inventory.transfers.show', $transfer)
            ->with('status', 'Transfer gönderildi');
    }

}
