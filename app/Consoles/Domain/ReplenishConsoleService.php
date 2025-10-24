<?php

namespace App\Consoles\Domain;

use App\Core\Contracts\SettingsReader;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;
use App\Modules\Inventory\Domain\Models\StockTransfer;
use App\Modules\Inventory\Domain\Models\StockTransferLine;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LogicException;

class ReplenishConsoleService
{
    public function __construct(
        private readonly SettingsReader $settings,
        private readonly ConnectionInterface $db,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(int $companyId, array $filters = []): array
    {
        $threshold = (float) Arr::get($filters, 'threshold', 0);
        $productId = Arr::get($filters, 'product_id');

        $balances = StockLedgerEntry::query()
            ->select([
                'product_id',
                'warehouse_id',
                DB::raw('COALESCE(SUM(qty_in - qty_out), 0) as balance'),
            ])
            ->where('company_id', $companyId)
            ->when($productId, fn (Builder $q) => $q->where('product_id', $productId))
            ->groupBy('product_id', 'warehouse_id')
            ->having('balance', '<=', $threshold)
            ->orderBy('balance')
            ->limit(50)
            ->get();

        $productIds = $balances->pluck('product_id')->unique()->filter()->all();
        $products = Product::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = $balances->map(function ($row) use ($products) {
            /** @var Product|null $product */
            $product = $products->get($row->product_id);
            $reorder = $product?->reorder_point ?: 0.0;

            return [
                'product_id' => $row->product_id,
                'product' => $product?->name,
                'sku' => $product?->sku,
                'warehouse_id' => $row->warehouse_id,
                'balance' => (float) $row->balance,
                'threshold' => $reorder,
            ];
        })->values()->all();

        $warehouses = Warehouse::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return [
            'low_stock' => $items,
            'warehouses' => $warehouses,
            'filters' => [
                'threshold' => $threshold,
                'product_id' => $productId,
            ],
        ];
    }

    /**
     * @param  array<int, array{product_id:int, qty:float}>
     */
    public function createTransfer(int $companyId, int $fromWarehouseId, int $toWarehouseId, array $lines): StockTransfer
    {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new LogicException(__('Kaynak ve hedef depo farklı olmalıdır.'));
        }

        if (empty($lines)) {
            throw new LogicException(__('Transfer için en az bir satır gereklidir.'));
        }

        $docNo = $this->generateDocNo($companyId);
        $userId = Auth::id();
        $timestamp = CarbonImmutable::now();

        return $this->db->transaction(function () use ($companyId, $fromWarehouseId, $toWarehouseId, $lines, $docNo, $userId, $timestamp) {
            $transfer = StockTransfer::create([
                'company_id' => $companyId,
                'doc_no' => $docNo,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'status' => 'draft',
            ]);

            foreach ($lines as $index => $line) {
                $qty = (float) Arr::get($line, 'qty');
                if ($qty <= 0) {
                    continue;
                }

                StockTransferLine::create([
                    'company_id' => $companyId,
                    'transfer_id' => $transfer->id,
                    'product_id' => Arr::get($line, 'product_id'),
                    'qty' => $qty,
                    'note' => Arr::get($line, 'note'),
                ]);
            }

            $transfer->load('lines');
            $this->postTransfer($transfer, $timestamp, $userId);

            return $transfer;
        });
    }

    private function generateDocNo(int $companyId): string
    {
        $settings = $this->settings->get($companyId);
        $prefix = Arr::get($settings->sequencing, 'shipment_prefix', 'TRF');
        $padding = max(3, min(8, (int) Arr::get($settings->sequencing, 'padding', 6)));

        $latest = StockTransfer::query()
            ->where('company_id', $companyId)
            ->where('doc_no', 'like', $prefix . '%')
            ->orderByDesc('doc_no')
            ->value('doc_no');

        $next = 1;
        if ($latest) {
            $numeric = (int) preg_replace('/[^0-9]/', '', substr($latest, strlen($prefix)));
            $next = $numeric + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $next, $padding, '0', STR_PAD_LEFT);
            $exists = StockTransfer::query()
                ->where('company_id', $companyId)
                ->where('doc_no', $candidate)
                ->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }

    private function postTransfer(StockTransfer $transfer, CarbonImmutable $timestamp, ?int $userId): void
    {
        if ($transfer->lines->isEmpty()) {
            throw new LogicException(__('Transfer satırı bulunmuyor.'));
        }

        foreach ($transfer->lines as $line) {
            $qty = (float) $line->qty;
            if ($qty <= 0) {
                continue;
            }

            StockLedgerEntry::create([
                'company_id' => $transfer->company_id,
                'product_id' => $line->product_id,
                'warehouse_id' => $transfer->from_warehouse_id,
                'bin_id' => $transfer->from_bin_id,
                'qty_in' => 0,
                'qty_out' => $qty,
                'reason' => 'transfer',
                'ref_type' => StockTransfer::class,
                'ref_id' => $transfer->id,
                'doc_no' => $transfer->doc_no,
                'dated_at' => $timestamp,
            ]);

            StockLedgerEntry::create([
                'company_id' => $transfer->company_id,
                'product_id' => $line->product_id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'bin_id' => $transfer->to_bin_id,
                'qty_in' => $qty,
                'qty_out' => 0,
                'reason' => 'transfer',
                'ref_type' => StockTransfer::class,
                'ref_id' => $transfer->id,
                'doc_no' => $transfer->doc_no,
                'dated_at' => $timestamp,
            ]);
        }

        $transfer->forceFill([
            'status' => 'posted',
            'posted_at' => $timestamp,
            'posted_by' => $userId,
        ])->save();
    }
}
