<?php

namespace App\Modules\Finance\Domain\Services;

use App\Core\Contracts\SettingsReader;
use App\Core\Domain\Sequencing\Sequencer;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\Receipt;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class NumberSequencer
{
    public function __construct(
        private readonly SettingsReader $settingsReader,
        private readonly Sequencer $sequencer,
    )
    {
    }

    public function nextInvoiceNumber(int $companyId): string
    {
        $settings = $this->settingsReader->get($companyId);
        $prefix = (string) Arr::get($settings->sequencing, 'invoice_prefix', 'INV');
        $padding = $this->padding(Arr::get($settings->sequencing, 'padding', 6));

        if ($this->useLegacySequencer()) {
            return $this->nextForModel($companyId, Invoice::class, 'doc_no', $prefix, $padding);
        }

        return $this->sequencer->next(
            $companyId,
            'invoice',
            $prefix,
            $padding,
            $this->resetPolicy($settings->sequencing)
        );
    }

    public function nextReceiptNumber(int $companyId): string
    {
        $settings = $this->settingsReader->get($companyId);
        $prefix = (string) Arr::get($settings->sequencing, 'receipt_prefix', 'RCPT');
        $padding = $this->padding(Arr::get($settings->sequencing, 'padding', 6));

        if ($this->useLegacySequencer()) {
            return $this->nextForModel($companyId, Receipt::class, 'doc_no', $prefix, $padding);
        }

        return $this->sequencer->next(
            $companyId,
            'receipt',
            $prefix,
            $padding,
            $this->resetPolicy($settings->sequencing)
        );
    }

    /**
     * @param class-string $model
     */
    protected function nextForModel(int $companyId, string $model, string $column, string $prefix, int $padding): string
    {
        /** @var \Illuminate\Database\Eloquent\Model $modelInstance */
        $modelInstance = new $model();
        $query = $modelInstance->newQuery()->where('company_id', $companyId);
        $latest = $query->where($column, 'like', $prefix . '%')->orderBy($column, 'desc')->value($column);

        $nextNumber = 1;
        if ($latest) {
            $numericPart = Str::after($latest, $prefix);
            $numericPart = preg_replace('/[^0-9]/', '', $numericPart) ?: '0';
            $nextNumber = (int) $numericPart + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
            $exists = $modelInstance->newQuery()
                ->where('company_id', $companyId)
                ->where($column, $candidate)
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

    protected function useLegacySequencer(): bool
    {
        return ! config('features.sequencer.v2', true);
    }
}
