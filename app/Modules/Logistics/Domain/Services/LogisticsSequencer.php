<?php

namespace App\Modules\Logistics\Domain\Services;

use App\Core\Contracts\SettingsReader;
use App\Core\Domain\Sequencing\Sequencer;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LogisticsSequencer
{
    public function __construct(
        private readonly SettingsReader $settingsReader,
        private readonly Sequencer $sequencer,
    ) {
    }

    public function nextShipment(int $companyId): string
    {
        return $this->nextNumber($companyId, Shipment::class, 'shipment_prefix', 'SHP', 'shipment');
    }

    public function nextReceipt(int $companyId): string
    {
        return $this->nextNumber($companyId, GoodsReceipt::class, 'grn_prefix', 'GRN', 'grn');
    }

    protected function nextNumber(int $companyId, string $modelClass, string $prefixKey, string $fallback, string $sequenceKey): string
    {
        $settings = $this->settingsReader->get($companyId);
        $prefix = (string) Arr::get($settings->sequencing, $prefixKey, $fallback);
        if ($prefix === '') {
            $prefix = $fallback;
        }

        $padding = $this->padding(Arr::get($settings->sequencing, 'padding', 6));

        if (! config('features.sequencer.v2', true)) {
            return $this->legacyNext($companyId, $modelClass, $prefix, $padding);
        }

        return $this->sequencer->next(
            $companyId,
            $sequenceKey,
            $prefix,
            $padding,
            $this->resetPolicy($settings->sequencing)
        );
    }

    protected function legacyNext(int $companyId, string $modelClass, string $prefix, int $padding): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $modelClass */
        $latest = $modelClass::query()
            ->where('company_id', $companyId)
            ->where('doc_no', 'like', $prefix . '%')
            ->orderBy('doc_no', 'desc')
            ->value('doc_no');

        $nextNumber = 1;
        if ($latest) {
            $numeric = Str::after($latest, $prefix);
            $numeric = preg_replace('/[^0-9]/', '', $numeric) ?: '0';
            $nextNumber = (int) $numeric + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
            $exists = $modelClass::query()
                ->where('company_id', $companyId)
                ->where('doc_no', $candidate)
                ->exists();
            $nextNumber++;
        } while ($exists);

        return $candidate;
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
}
