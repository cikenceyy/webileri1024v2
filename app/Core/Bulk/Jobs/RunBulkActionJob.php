<?php

namespace App\Core\Bulk\Jobs;

use App\Core\Bulk\Models\BulkJob;
use App\Modules\Inventory\Application\Console\InventoryBulkActionHandler;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Toplu iş kayıtlarını kuyruğa alıp ilgili modül işlemcisine devreder.
 */
class RunBulkActionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $bulkJobId)
    {
    }

    public function handle(): void
    {
        $job = BulkJob::query()->find($this->bulkJobId);
        if (! $job) {
            return;
        }

        $job->update([
            'status' => 'running',
            'started_at' => CarbonImmutable::now(),
            'progress' => 5,
        ]);

        try {
            $handler = $this->resolveHandler($job->module);
            $handler->handle($job);

            $job->refresh();
            $job->status = 'done';
            $job->progress = 100;
            $job->finished_at = CarbonImmutable::now();
            $job->error = null;
            $job->save();
        } catch (Throwable $exception) {
            Log::error('Bulk job çalışırken hata oluştu.', [
                'job_id' => $job->id,
                'message' => $exception->getMessage(),
            ]);

            $job->status = 'failed';
            $job->progress = 100;
            $job->finished_at = CarbonImmutable::now();
            $job->error = $exception->getMessage();
            $job->save();

            throw $exception;
        }
    }

    private function resolveHandler(string $module): InventoryBulkActionHandler
    {
        // Şimdilik Inventory modülü için tek handler; ileride registry ile genişletilecek.
        if ($module !== 'inventory') {
            throw new RuntimeException('Desteklenmeyen bulk modülü: ' . $module);
        }

        return App::make(InventoryBulkActionHandler::class);
    }
}
