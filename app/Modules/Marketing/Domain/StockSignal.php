<?php

namespace App\Modules\Marketing\Domain;

use App\Core\Contracts\SettingsReader;
use App\Modules\Inventory\Domain\Models\StockLedgerEntry;

class StockSignal
{
    public function __construct(private readonly SettingsReader $settings)
    {
    }

    /**
     * @return array{status:string,available:float,formatted:string}
     */
    public function forProduct(int $companyId, int $productId, ?int $safetyQty = null): array
    {
        $precision = $this->settings->get($companyId)->general['decimal_precision'] ?? 2;
        $available = (float) StockLedgerEntry::query()
            ->where('company_id', $companyId)
            ->where('product_id', $productId)
            ->selectRaw('COALESCE(SUM(qty_in - qty_out), 0) as balance')
            ->value('balance');

        $threshold = $safetyQty ?? 0.0;
        $status = 'in';

        if ($available <= 0.0) {
            $status = 'out';
        } elseif ($available <= $threshold) {
            $status = 'low';
        }

        return [
            'status' => $status,
            'available' => round($available, $precision),
            'formatted' => number_format($available, $precision, ',', '.'),
        ];
    }
}
