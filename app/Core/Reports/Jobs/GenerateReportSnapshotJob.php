<?php

namespace App\Core\Reports\Jobs;

use App\Core\Reports\Models\ReportSnapshot;
use App\Core\Reports\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Throwable;

/**
 * Cold rapor snapshot'larını kuyrukta üretir.
 */
class GenerateReportSnapshotJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $snapshotId)
    {
    }

    public function handle(): void
    {
        $snapshot = ReportSnapshot::query()->find($this->snapshotId);
        if (! $snapshot) {
            return;
        }

        $service = App::make(ReportService::class);
        $snapshot->update(['status' => 'running', 'error' => null]);
        $service->process($snapshot);
    }

    public function failed(Throwable $exception): void
    {
        $snapshot = ReportSnapshot::query()->find($this->snapshotId);
        if (! $snapshot) {
            return;
        }

        $snapshot->update([
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ]);
    }
}
