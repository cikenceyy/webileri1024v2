<?php

namespace App\Modules\Marketing\Application\Services;

class PricingService
{
    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array{subtotal: float, discount_total: float, tax_total: float, grand_total: float, lines: array<int, array<string, mixed>>}
     */
    public function calculate(array $lines): array
    {
        $subtotal = 0.0;
        $netTotal = 0.0;
        $taxTotal = 0.0;
        $sanitized = [];

        $defaultTaxRate = (float) (config('marketing.module.default_tax_rate') ?? 20);

        foreach ($lines as $index => $line) {
            $qty = (float) ($line['qty'] ?? 0);
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $discountRate = (float) ($line['discount_rate'] ?? 0);
            $taxRate = (float) ($line['tax_rate'] ?? $defaultTaxRate);

            $lineSubtotal = $qty * $unitPrice;
            $discountFactor = max(0, min(100, $discountRate)) / 100;
            $net = $lineSubtotal * (1 - $discountFactor);
            $tax = $net * max(0, min(100, $taxRate)) / 100;

            $subtotal += $lineSubtotal;
            $netTotal += $net;
            $taxTotal += $tax;

            $sanitized[] = [
                'product_id' => $line['product_id'] ?? null,
                'variant_id' => $line['variant_id'] ?? null,
                'description' => $line['description'] ?? '',
                'qty' => $qty,
                'unit' => $line['unit'] ?? 'pcs',
                'unit_price' => $unitPrice,
                'discount_rate' => $discountRate,
                'tax_rate' => $taxRate,
                'line_total' => round($net, 2),
                'sort_order' => $index,
            ];
        }

        $discountTotal = $subtotal - $netTotal;
        $grandTotal = $netTotal + $taxTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'lines' => $sanitized,
        ];
    }
}
