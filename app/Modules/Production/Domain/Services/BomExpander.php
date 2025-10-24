<?php

namespace App\Modules\Production\Domain\Services;

use App\Modules\Production\Domain\Models\Bom;
use Illuminate\Support\Collection;

class BomExpander
{
    public function expand(Bom $bom, float $targetQty, int $precision = 3): Collection
    {
        $bom->loadMissing('items.component', 'items.componentVariant');

        $baseOutput = (float) max($bom->output_qty, 0.0001);
        $multiplier = $targetQty / $baseOutput;

        return $bom->items
            ->sortBy('sort')
            ->map(function ($item) use ($multiplier, $precision) {
                $required = $multiplier * (float) $item->qty_per;
                $wastage = max(0.0, (float) $item->wastage_pct);
                $required *= (1 + ($wastage / 100));
                $required = round($required, $precision);

                return [
                    'item' => $item,
                    'required_qty' => $required,
                ];
            });
    }
}
