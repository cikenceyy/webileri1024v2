<?php

namespace App\Modules\Finance\Domain\Services;

use Illuminate\Support\Arr;

class InvoiceCalculator
{
    /**
     * @param array<int, array<string, mixed>> $lines
     * @return array{lines: array<int, array<string, mixed>>, totals: array{subtotal: float, tax: float, grand: float}}
     */
    public function calculate(array $lines, bool $taxInclusive): array
    {
        $hydrated = [];
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $grandTotal = 0.0;

        foreach ($lines as $index => $line) {
            $qty = (float) Arr::get($line, 'qty', 0);
            $unitPrice = (float) Arr::get($line, 'unit_price', 0);
            $discountPct = (float) Arr::get($line, 'discount_pct', 0);
            $taxRate = (float) Arr::get($line, 'tax_rate', 0);
            $rateFraction = $taxRate > 0 ? $taxRate / 100 : 0.0;

            $description = (string) Arr::get($line, 'description', '');
            if ($description === '') {
                continue;
            }

            $discountPct = max(0.0, min(100.0, $discountPct));
            $qty = max(0.0, $qty);
            $unitPrice = max(0.0, $unitPrice);

            if ($taxInclusive && $rateFraction > 0) {
                $gross = $qty * $unitPrice;
                $discountValue = $gross * ($discountPct / 100);
                $grossAfterDiscount = $gross - $discountValue;
                $net = $grossAfterDiscount / (1 + $rateFraction);
                $tax = $grossAfterDiscount - $net;
                $lineTotal = $grossAfterDiscount;
                $lineSubtotal = $net;
            } else {
                $base = $qty * $unitPrice;
                $discountValue = $base * ($discountPct / 100);
                $lineSubtotal = $base - $discountValue;
                $tax = $lineSubtotal * $rateFraction;
                $lineTotal = $lineSubtotal + $tax;
            }

            $lineSubtotal = round($lineSubtotal, 2);
            $tax = round($tax, 2);
            $lineTotal = round($lineTotal, 2);

            $hydrated[] = array_merge($line, [
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'discount_pct' => $discountPct,
                'tax_rate' => $taxRate,
                'line_subtotal' => $lineSubtotal,
                'line_tax' => $tax,
                'line_total' => $lineTotal,
                'sort' => $index,
            ]);

            $subtotal += $lineSubtotal;
            $taxTotal += $tax;
            $grandTotal += $lineTotal;
        }

        return [
            'lines' => $hydrated,
            'totals' => [
                'subtotal' => round($subtotal, 2),
                'tax' => round($taxTotal, 2),
                'grand' => round($grandTotal, 2),
            ],
        ];
    }
}
