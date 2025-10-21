<?php

namespace App\Modules\Inventory\Domain\Services;

use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Inventory\Domain\Models\ProductVariant;
use App\Modules\Inventory\Domain\Models\StockItem;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function reserveForOrder(Order $order): void
    {
        $order->loadMissing('lines.product', 'lines.variant');

        DB::transaction(function () use ($order): void {
            $warehouse = $this->resolveDefaultWarehouse($order->company_id);

            foreach ($order->lines as $index => $line) {
                $qty = (float) $line->qty;

                if ($qty <= 0 || ! $line->product) {
                    continue;
                }

                $product = $line->product;
                $variant = $line->variant;

                $this->assertSameCompany([
                    $order->company_id,
                    $warehouse->company_id,
                    $product->company_id,
                    $variant?->company_id,
                ]);

                $items = $this->lockStockItems($order->company_id, $product->id, $variant?->id);
                $stockItem = $items->firstWhere('warehouse_id', $warehouse->id)
                    ?? $this->findOrCreateStockItem($items, $warehouse, $product, $variant);

                $available = (float) $stockItem->qty - (float) $stockItem->reserved_qty;

                if ($qty > $available) {
                    throw ValidationException::withMessages([
                        "lines.{$index}.qty" => 'Rezerve edilebilir stok yetersiz.',
                    ]);
                }

                $stockItem->reserved_qty = (float) $stockItem->reserved_qty + $qty;
                $stockItem->save();
            }
        });
    }

    public function releaseReservedForOrder(Order $order): void
    {
        $order->loadMissing('lines.product', 'lines.variant');

        DB::transaction(function () use ($order): void {
            $warehouse = $this->resolveDefaultWarehouse($order->company_id);

            foreach ($order->lines as $line) {
                $qty = (float) $line->qty;

                if ($qty <= 0 || ! $line->product) {
                    continue;
                }

                $product = $line->product;
                $variant = $line->variant;

                $this->assertSameCompany([
                    $order->company_id,
                    $warehouse->company_id,
                    $product->company_id,
                    $variant?->company_id,
                ]);

                $items = $this->lockStockItems($order->company_id, $product->id, $variant?->id);
                $stockItem = $items->firstWhere('warehouse_id', $warehouse->id);

                if (! $stockItem) {
                    continue;
                }

                $reserved = (float) $stockItem->reserved_qty;
                $stockItem->reserved_qty = max(0, $reserved - $qty);
                $stockItem->save();
            }
        });
    }

    public function receive(Warehouse $warehouse, Product $product, ?ProductVariant $variant, float $qty, ?float $unitCost, array $context = []): StockMovement
    {
        $this->assertSameCompany([$warehouse->company_id, $product->company_id, $variant?->company_id]);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Giriş miktarı sıfırdan büyük olmalıdır.',
            ]);
        }

        return DB::transaction(function () use ($warehouse, $product, $variant, $qty, $unitCost, $context) {
            return $this->performReceive($warehouse, $product, $variant, $qty, $unitCost, $context);
        });
    }

    public function issue(Warehouse $warehouse, Product $product, ?ProductVariant $variant, float $qty, array $context = []): StockMovement
    {
        $this->assertSameCompany([$warehouse->company_id, $product->company_id, $variant?->company_id]);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Çıkış miktarı sıfırdan büyük olmalıdır.',
            ]);
        }

        $allowNegative = (bool) config('inventory.allow_negative_stock', false);

        return DB::transaction(function () use ($warehouse, $product, $variant, $qty, $context, $allowNegative) {
            return $this->performIssue($warehouse, $product, $variant, $qty, $context, $allowNegative)[0];
        });
    }

    public function transfer(Warehouse $from, Warehouse $to, Product $product, ?ProductVariant $variant, float $qty, array $context = []): array
    {
        $this->assertSameCompany([$from->company_id, $to->company_id, $product->company_id, $variant?->company_id]);

        if ($from->id === $to->id) {
            throw ValidationException::withMessages([
                'to_warehouse_id' => 'Transfer için farklı bir ambar seçmelisiniz.',
            ]);
        }

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Transfer miktarı sıfırdan büyük olmalıdır.',
            ]);
        }

        $allowNegative = (bool) config('inventory.allow_negative_stock', false);

        return DB::transaction(function () use ($from, $to, $product, $variant, $qty, $context, $allowNegative) {
            [$outMovement, $unitCost] = $this->performIssue($from, $product, $variant, $qty, array_merge($context, [
                'reason' => 'transfer',
            ]), $allowNegative);

            $inMovement = $this->performReceive($to, $product, $variant, $qty, $unitCost, array_merge($context, [
                'reason' => 'transfer',
            ]));

            return [$outMovement, $inMovement];
        });
    }

    public function adjust(Warehouse $warehouse, Product $product, ?ProductVariant $variant, float $qty, ?float $unitCost, array $context = []): StockMovement
    {
        $this->assertSameCompany([$warehouse->company_id, $product->company_id, $variant?->company_id]);

        if ($qty === 0.0) {
            throw ValidationException::withMessages([
                'qty' => 'Düzeltme miktarı sıfır olamaz.',
            ]);
        }

        $allowNegative = (bool) config('inventory.allow_negative_stock', false);

        return DB::transaction(function () use ($warehouse, $product, $variant, $qty, $unitCost, $context, $allowNegative) {
            $context = array_merge($context, ['reason' => 'adjustment']);

            if ($qty > 0) {
                return $this->performReceive($warehouse, $product, $variant, $qty, $unitCost, $context);
            }

            return $this->performIssue($warehouse, $product, $variant, abs($qty), $context, $allowNegative)[0];
        });
    }

    protected function performReceive(Warehouse $warehouse, Product $product, ?ProductVariant $variant, float $qty, ?float $unitCost, array $context): StockMovement
    {
        $companyId = $product->company_id;

        $items = $this->lockStockItems($companyId, $product->id, $variant?->id);
        $stockItem = $this->findOrCreateStockItem($items, $warehouse, $product, $variant);

        $summary = $this->currentTotals($companyId, $product->id, $variant?->id);

        if ($unitCost === null) {
            $unitCost = $summary['total_qty'] > 0 ? $summary['total_value'] / $summary['total_qty'] : 0.0;
        }

        $stockItem->qty = (float) $stockItem->qty + $qty;
        $stockItem->reorder_point = $stockItem->reorder_point ?: ($product->reorder_point ?? 0);
        $stockItem->save();

        return StockMovement::create([
            'company_id' => $companyId,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'direction' => StockMovement::DIRECTION_IN,
            'qty' => $qty,
            'unit_cost' => $unitCost,
            'reason' => $context['reason'] ?? 'purchase',
            'ref_type' => $context['ref_type'] ?? null,
            'ref_id' => $context['ref_id'] ?? null,
            'note' => $context['note'] ?? null,
            'moved_by' => $context['user_id'] ?? null,
            'moved_at' => $this->movedAt($context),
        ]);
    }

    protected function performIssue(Warehouse $warehouse, Product $product, ?ProductVariant $variant, float $qty, array $context, bool $allowNegative): array
    {
        $companyId = $product->company_id;

        $items = $this->lockStockItems($companyId, $product->id, $variant?->id);
        $stockItem = $items->firstWhere('warehouse_id', $warehouse->id);

        if (! $stockItem) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Seçilen ambar için stok bulunamadı.',
            ]);
        }

        $summary = $this->currentTotals($companyId, $product->id, $variant?->id);
        $onHand = $summary['total_qty'];

        if (! $allowNegative && $onHand < $qty) {
            throw ValidationException::withMessages([
                'qty' => 'Yeterli stok bulunmuyor.',
            ]);
        }

        $unitCost = $onHand > 0 ? $summary['total_value'] / $onHand : 0.0;

        $remaining = (float) $stockItem->qty - $qty;

        if (! $allowNegative && $remaining < 0) {
            throw ValidationException::withMessages([
                'qty' => 'Seçilen ambar için yeterli stok yok.',
            ]);
        }

        $stockItem->qty = $remaining;
        $stockItem->save();

        $movement = StockMovement::create([
            'company_id' => $companyId,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'direction' => StockMovement::DIRECTION_OUT,
            'qty' => $qty,
            'unit_cost' => $unitCost,
            'reason' => $context['reason'] ?? 'sale',
            'ref_type' => $context['ref_type'] ?? null,
            'ref_id' => $context['ref_id'] ?? null,
            'note' => $context['note'] ?? null,
            'moved_by' => $context['user_id'] ?? null,
            'moved_at' => $this->movedAt($context),
        ]);

        return [$movement, $unitCost];
    }

    protected function lockStockItems(int $companyId, int $productId, ?int $variantId): Collection
    {
        return StockItem::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->when($variantId !== null, fn ($q) => $q->where('variant_id', $variantId), fn ($q) => $q->whereNull('variant_id'))
            ->lockForUpdate()
            ->get();
    }

    protected function findOrCreateStockItem(Collection $items, Warehouse $warehouse, Product $product, ?ProductVariant $variant): StockItem
    {
        $existing = $items->firstWhere('warehouse_id', $warehouse->id);

        if ($existing) {
            return $existing;
        }

        return StockItem::create([
            'company_id' => $product->company_id,
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'qty' => 0,
            'reserved_qty' => 0,
            'reorder_point' => $product->reorder_point ?? 0,
        ]);
    }

    protected function resolveDefaultWarehouse(int $companyId): Warehouse
    {
        $warehouse = Warehouse::query()
            ->where('company_id', $companyId)
            ->where('is_default', true)
            ->first();

        if (! $warehouse) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Varsayılan depo bulunamadı.',
            ]);
        }

        return $warehouse;
    }

    protected function currentTotals(int $companyId, int $productId, ?int $variantId): array
    {
        $summary = StockMovement::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->when($variantId !== null, fn ($q) => $q->where('variant_id', $variantId), fn ($q) => $q->whereNull('variant_id'))
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'in' THEN qty ELSE -qty END), 0) as total_qty, COALESCE(SUM(CASE WHEN direction = 'in' THEN qty * unit_cost ELSE -qty * COALESCE(unit_cost, 0) END), 0) as total_value")
            ->first();

        return [
            'total_qty' => (float) ($summary->total_qty ?? 0),
            'total_value' => (float) ($summary->total_value ?? 0),
        ];
    }

    protected function movedAt(array $context): Carbon
    {
        $value = $context['moved_at'] ?? null;

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value) {
            return Carbon::parse($value);
        }

        return now();
    }

    protected function assertSameCompany(array $companies): void
    {
        $filtered = array_filter($companies, fn ($id) => $id !== null);
        $unique = array_unique($filtered);

        if (count($unique) > 1) {
            throw ValidationException::withMessages([
                'company_id' => 'Seçilen kayıtlar aynı şirkete ait olmalıdır.',
            ]);
        }
    }
}
