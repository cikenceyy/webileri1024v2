<?php

namespace App\Modules\Marketing\Domain;

use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\PriceListItem;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;

class PricelistBulkUpdater
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    /**
     * @param array{category_id?:int|null,search?:string|null} $filters
     * @param array{type:string,mode?:string,value:float,round?:float|null} $action
     * @return Collection<int, array{item:PriceListItem,old_price:float,new_price:float}>
     */
    public function preview(PriceList $priceList, array $filters, array $action): Collection
    {
        $query = PriceListItem::query()
            ->where('company_id', $priceList->company_id)
            ->where('price_list_id', $priceList->id)
            ->with('product');

        if (! empty($filters['category_id'])) {
            $query->whereHas('product', function ($builder) use ($filters): void {
                $builder->where('category_id', $filters['category_id']);
            });
        }

        if (! empty($filters['search'])) {
            $term = '%' . trim((string) $filters['search']) . '%';
            $query->whereHas('product', function ($builder) use ($term): void {
                $builder->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term);
            });
        }

        $items = $query->get();

        return $items->map(function (PriceListItem $item) use ($action): array {
            $old = (float) $item->price;
            $new = $this->calculateNewPrice($old, $action);

            return [
                'item' => $item,
                'old_price' => $old,
                'new_price' => $new,
            ];
        })->filter(fn (array $row): bool => $row['new_price'] !== $row['old_price']);
    }

    /**
     * @param array{category_id?:int|null,search?:string|null} $filters
     * @param array{type:string,mode?:string,value:float,round?:float|null} $action
     * @return Collection<int, array{item_id:int,old_price:float,new_price:float}>
     */
    public function apply(PriceList $priceList, array $filters, array $action): Collection
    {
        $preview = $this->preview($priceList, $filters, $action);

        $this->db->transaction(function () use ($preview): void {
            foreach ($preview as $row) {
                /** @var PriceListItem $item */
                $item = $row['item'];
                $item->update(['price' => $row['new_price']]);
            }
        });

        return $preview->map(static function (array $row): array {
            /** @var PriceListItem $item */
            $item = $row['item'];

            return [
                'item_id' => $item->id,
                'old_price' => $row['old_price'],
                'new_price' => $row['new_price'],
            ];
        });
    }

    /**
     * @param array{type:string,mode?:string,value:float,round?:float|null} $action
     */
    protected function calculateNewPrice(float $base, array $action): float
    {
        $value = (float) ($action['value'] ?? 0);
        $mode = $action['mode'] ?? 'increase';
        $round = (float) ($action['round'] ?? 0.0);

        $updated = $base;

        switch ($action['type']) {
            case 'percent':
                $delta = $base * ($value / 100);
                $updated = $mode === 'decrease' ? $base - $delta : $base + $delta;
                break;
            case 'fixed':
            default:
                $updated = $mode === 'decrease' ? $base - $value : $base + $value;
                break;
        }

        if ($round > 0) {
            $updated = round($updated / $round) * $round;
        }

        return max(0.0, round($updated, 4));
    }
}
