<?php

namespace App\Modules\Inventory\Domain\Services;

use App\Core\Contracts\SettingsReader;
use App\Core\Domain\Sequencing\Sequencer;
use Illuminate\Support\Arr;

class InventorySequencer
{
    public function __construct(
        private readonly SettingsReader $settingsReader,
        private readonly Sequencer $sequencer,
    ) {
    }

    public function nextTransferNumber(int $companyId): string
    {
        $settings = $this->settingsReader->get($companyId);
        $sequencing = $settings->sequencing;
        $prefix = (string) Arr::get($sequencing, 'transfer_prefix', Arr::get($sequencing, 'shipment_prefix', 'TRF'));
        if ($prefix === '') {
            $prefix = 'TRF';
        }

        $padding = $this->padding(Arr::get($sequencing, 'padding', 6));

        if (! config('features.sequencer.v2', true)) {
            return $this->legacyTransferNumber($companyId, $prefix, $padding);
        }

        return $this->sequencer->next(
            $companyId,
            'transfer',
            $prefix,
            $padding,
            $this->resetPolicy($sequencing)
        );
    }

    public function nextStockCountNumber(int $companyId): string
    {
        $settings = $this->settingsReader->get($companyId);
        $sequencing = $settings->sequencing;
        $prefix = (string) Arr::get($sequencing, 'stock_count_prefix', Arr::get($sequencing, 'shipment_prefix', 'CNT'));
        if ($prefix === '') {
            $prefix = 'CNT';
        }

        $padding = $this->padding(Arr::get($sequencing, 'padding', 6));

        if (! config('features.sequencer.v2', true)) {
            return $this->legacyStockCountNumber($companyId, $prefix, $padding);
        }

        return $this->sequencer->next(
            $companyId,
            'stock_count',
            $prefix,
            $padding,
            $this->resetPolicy($sequencing)
        );
    }

    protected function padding(mixed $value): int
    {
        $padding = (int) $value;

        return max(3, min(8, $padding));
    }

    protected function resetPolicy(array $sequencing): string
    {
        $policy = (string) ($sequencing['reset_policy'] ?? 'yearly');

        return in_array($policy, ['yearly', 'never'], true) ? $policy : 'yearly';
    }

    protected function legacyTransferNumber(int $companyId, string $prefix, int $padding): string
    {
        $base = $prefix . '-' . now()->format('ym');
        $counter = \App\Modules\Inventory\Domain\Models\StockTransfer::query()
            ->where('company_id', $companyId)
            ->where('doc_no', 'like', $base . '%')
            ->count() + 1;

        return sprintf('%s%s', $base, str_pad((string) $counter, $padding, '0', STR_PAD_LEFT));
    }

    protected function legacyStockCountNumber(int $companyId, string $prefix, int $padding): string
    {
        $base = $prefix . '-CNT-' . now()->format('ym');
        $counter = \App\Modules\Inventory\Domain\Models\StockCount::query()
            ->where('company_id', $companyId)
            ->where('doc_no', 'like', $base . '%')
            ->count() + 1;

        return sprintf('%s%s', $base, str_pad((string) $counter, $padding, '0', STR_PAD_LEFT));
    }
}
