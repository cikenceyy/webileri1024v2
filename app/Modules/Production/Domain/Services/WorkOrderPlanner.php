<?php

namespace App\Modules\Production\Domain\Services;

use App\Modules\Marketing\Domain\Models\OrderLine as LegacyOrderLine;
use App\Modules\Marketing\Domain\Models\SalesOrderLine;
use App\Modules\Production\Domain\Models\Bom;
use App\Modules\Production\Domain\Models\WorkOrder;

class WorkOrderPlanner
{
    public function __construct(private readonly WorkOrderSequencer $sequencer)
    {
    }

    public function createFromOrderLine(SalesOrderLine|LegacyOrderLine $line): ?WorkOrder
    {
        $sourceType = $line instanceof SalesOrderLine ? 'sales_order_line' : 'order_line';

        $existing = WorkOrder::query()
            ->where('company_id', $line->company_id)
            ->where('source_type', $sourceType)
            ->where('source_id', $line->getKey())
            ->first();

        if ($existing) {
            return $existing;
        }

        $bom = $this->resolveBom($line);
        if (! $bom) {
            return null;
        }

        $docNo = $this->sequencer->next((int) $line->company_id);

        return WorkOrder::create([
            'company_id' => $line->company_id,
            'doc_no' => $docNo,
            'product_id' => $line->product_id,
            'variant_id' => $line->variant_id,
            'bom_id' => $bom->getKey(),
            'target_qty' => $line->qty,
            'uom' => $line->uom ?? $line->unit ?? 'pcs',
            'status' => 'draft',
            'due_date' => optional($line->order)->due_date,
            'source_type' => $sourceType,
            'source_id' => $line->getKey(),
        ]);
    }

    protected function resolveBom(SalesOrderLine|LegacyOrderLine $line): ?Bom
    {
        $query = Bom::query()
            ->where('company_id', $line->company_id)
            ->where('product_id', $line->product_id)
            ->orderByDesc('is_active')
            ->orderByDesc('version');

        if ($line->variant_id) {
            $query->orderByRaw('variant_id = ? desc', [$line->variant_id])
                ->orderByRaw('variant_id is null');
        }

        $bom = $query->first();

        if (! $bom && $line->variant_id) {
            $bom = Bom::query()
                ->where('company_id', $line->company_id)
                ->where('product_id', $line->product_id)
                ->whereNull('variant_id')
                ->orderByDesc('is_active')
                ->orderByDesc('version')
                ->first();
        }

        return $bom;
    }
}
