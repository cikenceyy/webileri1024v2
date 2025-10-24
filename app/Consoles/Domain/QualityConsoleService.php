<?php

namespace App\Consoles\Domain;

use App\Consoles\Domain\Models\QualityCheck;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use App\Modules\Logistics\Domain\Models\Shipment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class QualityConsoleService
{

    /**
     * @return array<string, mixed>
     */
    public function summary(int $companyId): array
    {
        $today = CarbonImmutable::now()->toDateString();

        $incoming = GoodsReceipt::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['received', 'reconciled'])
            ->whereDate('received_at', $today)
            ->orderByDesc('received_at')
            ->limit(20)
            ->get();

        $outgoing = Shipment::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['packed'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get();

        $checkMap = $this->loadChecks($companyId, $incoming, $outgoing);

        return [
            'incoming' => $incoming->map(fn (GoodsReceipt $receipt) => $this->formatSubject($receipt, 'incoming', $checkMap))->all(),
            'outgoing' => $outgoing->map(fn (Shipment $shipment) => $this->formatSubject($shipment, 'outgoing', $checkMap))->all(),
        ];
    }

    public function record(int $companyId, string $subjectType, int $subjectId, string $direction, string $result, ?string $notes = null): void
    {
        $userId = Auth::id();

        QualityCheck::create([
            'company_id' => $companyId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'direction' => $direction,
            'result' => $result,
            'notes' => $notes,
            'checked_at' => CarbonImmutable::now(),
            'checked_by' => $userId,
        ]);
    }

    public function hasBlockingFailure(int $companyId, string $subjectType, int $subjectId): bool
    {
        return QualityCheck::query()
            ->where('company_id', $companyId)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->where('result', 'fail')
            ->exists();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, GoodsReceipt|Shipment>  $incoming
     * @param  \Illuminate\Support\Collection<int, Shipment>  $outgoing
     * @return array<string, array<string, mixed>>
     */
    private function loadChecks(int $companyId, $incoming, $outgoing): array
    {
        $keys = collect([$incoming, $outgoing])
            ->flatten()
            ->map(fn ($model) => [$model instanceof GoodsReceipt ? GoodsReceipt::class : Shipment::class, $model->getKey()])
            ->unique()
            ->values();

        if ($keys->isEmpty()) {
            return [];
        }

        $query = QualityCheck::query()
            ->where('company_id', $companyId)
            ->where(function ($q) use ($keys): void {
                foreach ($keys as $pair) {
                    [$type, $id] = $pair;
                    $q->orWhere(function ($sub) use ($type, $id): void {
                        $sub->where('subject_type', $type)
                            ->where('subject_id', $id);
                    });
                }
            })
            ->orderByDesc('checked_at');

        $map = [];
        foreach ($query->get() as $check) {
            $key = $check->subject_type . ':' . $check->subject_id;
            if (isset($map[$key])) {
                continue;
            }
            $map[$key] = [
                'result' => $check->result,
                'notes' => $check->notes,
                'checked_at' => optional($check->checked_at)->toDateTimeString(),
            ];
        }

        return $map;
    }

    private function formatSubject($model, string $direction, array $checkMap): array
    {
        $type = $model instanceof GoodsReceipt ? GoodsReceipt::class : Shipment::class;
        $key = $type . ':' . $model->getKey();
        $check = $checkMap[$key] ?? null;

        return [
            'id' => $model->getKey(),
            'doc_no' => $model->doc_no,
            'status' => $model->status,
            'direction' => $direction,
            'last_check' => $check,
        ];
    }
}
