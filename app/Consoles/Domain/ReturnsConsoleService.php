<?php

namespace App\Consoles\Domain;

use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\GoodsReceiptLine;
use App\Modules\Logistics\Domain\Services\LogisticsSequencer;
use App\Modules\Marketing\Domain\Models\ReturnRequest;
use App\Modules\Marketing\Domain\Models\ReturnRequestLine;
use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;
use LogicException;

class ReturnsConsoleService
{
    public function __construct(
        private readonly LogisticsSequencer $sequencer,
        private readonly ConnectionInterface $db,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(int $companyId, array $filters = []): array
    {
        $status = Arr::get($filters, 'status');

        $returns = ReturnRequest::query()
            ->with(['customer', 'lines'])
            ->where('company_id', $companyId)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return [
            'returns' => $returns->map(fn (ReturnRequest $request) => $this->formatReturn($request))->all(),
            'filters' => ['status' => $status],
        ];
    }

    /**
     * @param  array<int>  $returnIds
     */
    public function approve(int $companyId, array $returnIds): void
    {
        ReturnRequest::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $returnIds)
            ->update(['status' => ReturnRequest::STATUS_APPROVED]);
    }

    /**
     * @param  array<int>  $returnIds
     */
    public function close(int $companyId, array $returnIds): void
    {
        ReturnRequest::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $returnIds)
            ->update(['status' => ReturnRequest::STATUS_CLOSED]);
    }

    /**
     * @param  array<int>  $returnIds
     * @return array<int>
     */
    public function createReceipts(int $companyId, array $returnIds): array
    {
        $requests = ReturnRequest::query()
            ->with('lines')
            ->where('company_id', $companyId)
            ->whereIn('id', $returnIds)
            ->get();

        if ($requests->isEmpty()) {
            return [];
        }

        $created = [];

        $this->db->transaction(function () use ($requests, $companyId, &$created): void {
            foreach ($requests as $request) {
                if ($request->lines->isEmpty()) {
                    continue;
                }

                $docNo = $this->sequencer->nextReceipt($companyId);
                $receipt = GoodsReceipt::create([
                    'company_id' => $companyId,
                    'doc_no' => $docNo,
                    'vendor_id' => $request->customer_id,
                    'source_type' => ReturnRequest::class,
                    'source_id' => $request->getKey(),
                    'status' => 'draft',
                    'received_at' => CarbonImmutable::now(),
                ]);

                foreach ($request->lines as $index => $line) {
                    GoodsReceiptLine::create([
                        'company_id' => $companyId,
                        'receipt_id' => $receipt->id,
                        'product_id' => $line->product_id,
                        'variant_id' => $line->variant_id,
                        'qty_expected' => $line->qty,
                        'qty_received' => $line->qty,
                        'source_line_type' => ReturnRequestLine::class,
                        'source_line_id' => $line->id,
                        'sort' => $index,
                        'variance_reason' => 'return',
                    ]);
                }

                $created[] = $receipt->getKey();
            }
        });

        return $created;
    }

    private function formatReturn(ReturnRequest $request): array
    {
        return [
            'id' => $request->getKey(),
            'customer' => optional($request->customer)->name,
            'status' => $request->status,
            'reason' => $request->reason,
            'lines' => $request->lines->count(),
            'created_at' => optional($request->created_at)->toDateTimeString(),
        ];
    }
}
