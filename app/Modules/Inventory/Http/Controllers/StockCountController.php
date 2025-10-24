<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Core\Contracts\SettingsReader;
use App\Http\Controllers\Controller;
use App\Modules\Inventory\Domain\Models\StockCount;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Inventory\Http\Requests\SaveStockCountRequest;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockCountController extends Controller
{
    public function __construct(
        protected SettingsReader $settingsReader,
        protected DatabaseManager $database,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', StockCount::class);

        $counts = StockCount::query()
            ->with(['warehouse', 'bin'])
            ->where('company_id', Auth::user()->company_id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('inventory::counts.index', [
            'counts' => $counts,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StockCount::class);

        $companyId = Auth::user()->company_id;
        $settings = $this->settingsReader->get($companyId);
        $defaults = $settings->defaults;
        $sequencing = $settings->sequencing;

        $nextDoc = $this->formatDocumentNumber($sequencing['shipment_prefix'] ?? 'CNT', $sequencing['padding'] ?? 6);

        $warehouses = Warehouse::query()
            ->with('bins')
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('inventory::counts.create', [
            'warehouses' => $warehouses,
            'defaultWarehouse' => $defaults['warehouse_id'] ?? null,
            'nextDoc' => $nextDoc,
        ]);
    }

    public function store(SaveStockCountRequest $request): RedirectResponse
    {
        $this->authorize('create', StockCount::class);

        $companyId = Auth::user()->company_id;

        $count = new StockCount([
            'company_id' => $companyId,
            'doc_no' => $request->input('doc_no') ?: $this->generateDocumentNumber($companyId),
            'warehouse_id' => $request->integer('warehouse_id'),
            'bin_id' => $request->input('bin_id'),
            'status' => 'draft',
        ]);

        $count->save();

        foreach ($request->input('lines', []) as $lineData) {
            $count->lines()->create([
                'company_id' => $companyId,
                'product_id' => $lineData['product_id'],
                'qty_expected' => $lineData['qty_expected'] ?? null,
                'qty_counted' => $lineData['qty_counted'],
                'diff_cached' => ($lineData['qty_counted'] ?? 0) - ($lineData['qty_expected'] ?? 0),
            ]);
        }

        return redirect()
            ->route('admin.inventory.counts.show', $count)
            ->with('status', 'Sayım oluşturuldu');
    }

    public function show(StockCount $count): View
    {
        $this->authorize('view', $count);

        $count->load(['lines.product', 'warehouse', 'bin']);

        return view('inventory::counts.show', [
            'count' => $count,
        ]);
    }

    public function markCounted(StockCount $count): RedirectResponse
    {
        $this->authorize('update', $count);

        if ($count->status !== 'draft') {
            return back()->withErrors('Sayım zaten işaretlenmiş.');
        }

        $count->forceFill([
            'status' => 'counted',
            'counted_at' => Carbon::now(),
        ])->save();

        return redirect()
            ->route('admin.inventory.counts.show', $count)
            ->with('status', 'Sayım counted olarak işaretlendi');
    }

    public function reconcile(StockCount $count): RedirectResponse
    {
        $this->authorize('reconcile', $count);

        if ($count->status === 'reconciled') {
            return back()->withErrors('Sayım zaten mutabık.');
        }

        $companyId = Auth::user()->company_id;
        $count->loadMissing('lines');

        $this->database->transaction(function () use ($count, $companyId) {
            $now = Carbon::now();

            foreach ($count->lines as $line) {
                $difference = ($line->qty_counted ?? 0) - ($line->qty_expected ?? 0);

                if ($difference == 0.0) {
                    continue;
                }

                StockLedgerEntry::create([
                    'company_id' => $companyId,
                    'product_id' => $line->product_id,
                    'warehouse_id' => $count->warehouse_id,
                    'bin_id' => $count->bin_id,
                    'qty_in' => $difference > 0 ? $difference : 0,
                    'qty_out' => $difference < 0 ? abs($difference) : 0,
                    'reason' => 'count_adjust',
                    'ref_type' => StockCount::class,
                    'ref_id' => $count->id,
                    'doc_no' => $count->doc_no,
                    'dated_at' => $now,
                ]);

                $line->update(['diff_cached' => $difference]);
            }

            $count->forceFill([
                'status' => 'reconciled',
                'reconciled_at' => $now,
            ])->save();
        });

        return redirect()
            ->route('admin.inventory.counts.show', $count)
            ->with('status', 'Sayım mutabıklandı');
    }

    protected function formatDocumentNumber(string $prefix, int $padding): string
    {
        $sequence = now()->format('ymd');

        return sprintf('%s-%s', $prefix, str_pad($sequence, $padding, '0', STR_PAD_LEFT));
    }

    protected function generateDocumentNumber(int $companyId): string
    {
        $settings = $this->settingsReader->get($companyId);
        $prefix = $settings->sequencing['shipment_prefix'] ?? 'CNT';
        $padding = $settings->sequencing['padding'] ?? 6;

        $base = $prefix . '-CNT-' . now()->format('ym');
        $counter = StockCount::query()
            ->where('company_id', $companyId)
            ->where('doc_no', 'like', $base . '%')
            ->count() + 1;

        return sprintf('%s%s', $base, str_pad((string) $counter, $padding, '0', STR_PAD_LEFT));
    }
}
