<?php

namespace App\Modules\Drive\Console\Commands;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DriveRefreshMeta extends Command
{
    protected $signature = 'drive:refresh-meta
        {--company=* : Sadece belirtilen şirket kimlikleri için çalıştır.}
        {--missing-size : Boyutu eksik veya sıfır olan kayıtları hedefle.}
        {--limit=0 : İşlenecek maksimum kayıt sayısı.}
        {--hash : SHA-256 karmasını yeniden hesapla.}';

    protected $description = 'Drive dosya meta verilerini yeniden oluşturur (boyut, karma vb.).';

    public function handle(): int
    {
        $companyIds = array_filter(array_map('intval', (array) $this->option('company')));
        $limit = (int) $this->option('limit');
        $onlyMissingSize = (bool) $this->option('missing-size');
        $recalculateHash = (bool) $this->option('hash');

        $query = Media::query()->orderBy('id');

        if ($companyIds) {
            $query->whereIn('company_id', $companyIds);
        }

        if ($onlyMissingSize) {
            $query->where(function ($q) {
                $q->whereNull('size')->orWhere('size', 0);
            });
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $total = 0;
        $updated = 0;
        $failed = 0;

        $query->chunkById(100, function ($medias) use (&$total, &$updated, &$failed, $onlyMissingSize, $recalculateHash) {
            foreach ($medias as $media) {
                $total++;

                try {
                    $disk = Storage::disk($media->disk);

                    if (! $disk->exists($media->path)) {
                        $this->warn(sprintf('Dosya bulunamadı: [%s] %s', $media->disk, $media->path));
                        continue;
                    }

                    $changes = [];

                    if ($onlyMissingSize || ! $media->size) {
                        $changes['size'] = $disk->size($media->path) ?: $media->size;
                    }

                    if ($recalculateHash) {
                        $hash = $this->streamHash($disk->readStream($media->path));
                        $changes['sha256'] = $hash ?: $media->sha256;
                    }

                    if (! empty($changes)) {
                        $media->fill($changes);
                        $media->save();
                        $updated++;
                    }
                } catch (\Throwable $exception) {
                    report($exception);
                    $failed++;
                }
            }
        });

        $this->info(sprintf('Toplam: %d, güncellenen: %d, hatalı: %d', $total, $updated, $failed));

        return Command::SUCCESS;
    }

    protected function streamHash($stream): ?string
    {
        if (! $stream) {
            return null;
        }

        try {
            $context = hash_init('sha256');

            while (! feof($stream)) {
                $chunk = fread($stream, 1024 * 512);

                if ($chunk === false) {
                    break;
                }

                hash_update($context, $chunk);
            }

            fclose($stream);

            return hash_final($context);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
