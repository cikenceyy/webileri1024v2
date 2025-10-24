<?php

namespace App\Modules\Production\Domain\Services;

use App\Core\Contracts\SettingsReader;
use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WorkOrderSequencer
{
    public function __construct(private readonly SettingsReader $settingsReader)
    {
    }

    public function next(int $companyId): string
    {
        $settings = $this->settingsReader->get($companyId);
        $prefix = (string) Arr::get($settings->sequencing, 'work_order_prefix', 'WO');
        if ($prefix === '') {
            $prefix = 'WO';
        }
        $padding = $this->padding(Arr::get($settings->sequencing, 'padding', 6));

        $query = WorkOrder::query()->where('company_id', $companyId);
        $latest = $query->where('doc_no', 'like', $prefix . '%')->orderBy('doc_no', 'desc')->value('doc_no');

        $nextNumber = 1;
        if ($latest) {
            $numeric = Str::after($latest, $prefix);
            $numeric = preg_replace('/[^0-9]/', '', $numeric) ?: '0';
            $nextNumber = (int) $numeric + 1;
        }

        do {
            $candidate = $prefix . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
            $exists = WorkOrder::query()
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
}
