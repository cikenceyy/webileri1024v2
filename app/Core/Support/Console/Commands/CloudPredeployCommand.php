<?php

namespace App\Core\Support\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class CloudPredeployCommand extends Command
{
    protected $signature = 'webileri:cloud:predeploy {--skip-cache : Cache komutlarını atla (debug amaçlı)}';

    protected $description = 'Prod/staging deploy öncesi cache ve optimizasyon adımlarını koşturur.';

    public function handle(): int
    {
        if ($this->option('skip-cache')) {
            $this->components->info('Cache komutları atlandı.');
            return self::SUCCESS;
        }

        $steps = [
            'config:cache',
            'route:cache',
            'view:cache',
            'event:cache',
        ];

        foreach ($steps as $command) {
            try {
                $this->components->task($command, function () use ($command): void {
                    Artisan::call($command);
                });
            } catch (Throwable $exception) {
                $this->components->error(sprintf('%s komutu hata verdi: %s', $command, $exception->getMessage()));

                return self::FAILURE;
            }
        }

        $this->components->info('Ön hazırlık cache adımları tamamlandı.');

        return self::SUCCESS;
    }
}
