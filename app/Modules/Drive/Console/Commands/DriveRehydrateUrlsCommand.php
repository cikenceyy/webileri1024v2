<?php

namespace App\Modules\Drive\Console\Commands;

use App\Modules\Drive\Domain\DriveStorage;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Console\Command;

class DriveRehydrateUrlsCommand extends Command
{
    protected $signature = 'drive:rehydrate-urls {--chunk=100 : Kaç kayıtın aynı anda işleneceği} {--dry-run : Sadece kontrol et, güncelleme yapma}';

    protected $description = 'Mevcut medya kayıtları için imzalı URL önizlemesini test eder ve isteğe bağlı olarak önbelleği yeniler.';

    public function handle(DriveStorage $storage): int
    {
        $chunk = (int) $this->option('chunk');
        $dry = (bool) $this->option('dry-run');
        $count = 0;
        $failures = 0;

        Media::query()->orderBy('id')->chunk($chunk, function ($items) use (&$count, &$failures, $storage, $dry): void {
            foreach ($items as $media) {
                $count++;
                $url = $storage->temporaryUrl($media);

                if (! $url) {
                    $failures++;
                    $this->components->error("[{$media->id}] URL üretilemedi");
                    continue;
                }

                if (! $dry) {
                    $media->touch();
                }

                $this->components->twoColumnDetail("#{$media->id}", $url);
            }
        });

        $this->newLine();
        $this->components->info("Toplam kayıt: {$count}");
        $this->components->info("Başarısız URL: {$failures}");

        if ($failures > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
