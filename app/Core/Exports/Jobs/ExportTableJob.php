<?php

namespace App\Core\Exports\Jobs;

use App\Core\Exports\Mail\ExportReadyMail;
use App\Core\Exports\Models\TableExport;
use App\Core\Exports\Support\SimpleXlsxWriter;
use App\Core\Mail\NotificationMailService;
use App\Core\TableKit\QueryAdapter;
use App\Core\TableKit\TableExporterRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * TableKit export isteklerini kuyruğa alıp dosya üreten job sınıfı.
 */
class ExportTableJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $exportId)
    {
    }

    public function handle(TableExporterRegistry $registry, NotificationMailService $mailService): void
    {
        $export = TableExport::query()->find($this->exportId);
        if (! $export || $export->status !== TableExport::STATUS_PENDING) {
            return;
        }

        $export->update([
            'status' => TableExport::STATUS_RUNNING,
            'progress' => 5,
        ]);

        try {
            $resolver = $registry->resolve($export->table_key);
            $definition = app()->call($resolver);

            if (! is_array($definition) || ! isset($definition['builder'], $definition['configure'], $definition['columns'])) {
                throw new \RuntimeException('Export yapılandırması eksik.');
            }

            $builder = $definition['builder'];
            $configure = $definition['configure'];
            $columns = $definition['columns'];
            $map = $definition['map'] ?? null;

            /** @var QueryAdapter $adapter */
            $adapter = $configure(QueryAdapter::make($builder, $export->table_key));
            $params = $export->params ?? [];
            $state = $params['state'] ?? [];

            if (($state === null || $state === []) && ! empty($params['query'])) {
                $queryString = ltrim((string) $params['query'], '?');
                parse_str($queryString, $queryParams);
                $fakeRequest = Request::create('/', 'GET', $queryParams);
                $state = $adapter->previewState($fakeRequest);
            }

            $exportBuilder = clone $builder;
            $adapter->applyStateToBuilder($exportBuilder, $state);

            $disk = Storage::disk('local');
            $directory = 'exports/' . now()->format('Y/m/d');
            $disk->makeDirectory($directory);
            $filename = Str::uuid()->toString() . ($export->format === 'xlsx' ? '.xlsx' : '.csv');
            $path = $directory . '/' . $filename;
            $fullPath = $disk->path($path);

            $rowsProcessed = 0;
            $this->updateProgress($export, 15);

            if ($export->format === 'xlsx') {
                $writer = new SimpleXlsxWriter(array_values($columns));

                foreach ($exportBuilder->cursor() as $model) {
                    $rowsProcessed++;
                    $row = $this->mapRow($model, $columns, $map);
                    $writer->addRow($row);

                    if ($rowsProcessed % 200 === 0) {
                        $this->updateProgress($export, $this->approximateProgress($rowsProcessed));
                    }
                }

                $writer->saveTo($fullPath);
            } else {
                $handle = fopen($fullPath, 'w');
                fputcsv($handle, array_values($columns));

                foreach ($exportBuilder->cursor() as $model) {
                    $rowsProcessed++;
                    $row = $this->mapRow($model, $columns, $map);
                    fputcsv($handle, $row);

                    if ($rowsProcessed % 200 === 0) {
                        $this->updateProgress($export, $this->approximateProgress($rowsProcessed));
                    }
                }

                fclose($handle);
            }

            $export->update([
                'status' => TableExport::STATUS_DONE,
                'file_path' => $path,
                'row_count' => $rowsProcessed,
                'progress' => 100,
            ]);

            $mailService->send($export->company_id, new ExportReadyMail($export), [
                'context' => 'table_export',
            ]);
        } catch (Throwable $exception) {
            $export->update([
                'status' => TableExport::STATUS_FAILED,
                'error' => $exception->getMessage(),
                'progress' => 100,
            ]);

            report($exception);
        }
    }

    /**
     * @param  array<string, string>  $columns
     * @param  callable|null  $map
     * @return array<int, mixed>
     */
    private function mapRow(mixed $model, array $columns, ?callable $map): array
    {
        $data = $map ? $map($model) : (array) $model->toArray();
        $row = [];

        foreach (array_keys($columns) as $key) {
            $row[] = $data[$key] ?? '';
        }

        return $row;
    }

    private function updateProgress(TableExport $export, int $progress): void
    {
        $progress = min(95, max($progress, 5));

        if ($progress <= (int) $export->progress) {
            return;
        }

        TableExport::query()->whereKey($export->id)->update([
            'progress' => $progress,
            'updated_at' => now(),
        ]);

        $export->progress = $progress;
    }

    private function approximateProgress(int $rowsProcessed): int
    {
        return min(95, 15 + (int) floor($rowsProcessed / 20));
    }
}
