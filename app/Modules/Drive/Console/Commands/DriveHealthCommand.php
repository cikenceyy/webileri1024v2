<?php

namespace App\Modules\Drive\Console\Commands;

use App\Modules\Drive\Domain\DriveStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DriveHealthCommand extends Command
{
    protected $signature = 'drive:health';

    protected $description = 'Kontrol disk yapılandırması, erişilebilirlik ve okuma/yazma sağlığını raporlar.';

    public function handle(DriveStorage $storage): int
    {
        $disk = $storage->diskName();
        $config = config("filesystems.disks.{$disk}", []);
        $filesystem = $storage->filesystem($disk);
        $driver = $config['driver'] ?? 'unknown';
        $bucket = $config['bucket'] ?? 'n/a';
        $endpoint = $config['endpoint'] ?? $config['url'] ?? null;
        $isLink = file_exists(public_path('storage')) ? is_link(public_path('storage')) : false;

        $this->components->info('Drive Health Check');
        $this->components->twoColumnDetail('Aktif disk', $disk);
        $this->components->twoColumnDetail('Sürücü', $driver);
        $this->components->twoColumnDetail('Bucket/Root', $bucket);
        $this->components->twoColumnDetail('Endpoint', $endpoint ?: 'n/a');
        $this->components->twoColumnDetail('storage:link', $isLink ? 'OK' : 'Eksik');

        $testPath = trim('health-check/' . Str::ulid() . '.txt', '/');
        $payload = 'drive-health:' . now()->toIso8601String();
        $writeOk = false;
        $readOk = false;
        $deleteOk = false;

        try {
            $filesystem->put($testPath, $payload, ['visibility' => 'private']);
            $writeOk = true;
            $readOk = $filesystem->exists($testPath) && $filesystem->get($testPath) === $payload;
            $filesystem->delete($testPath);
            $deleteOk = ! $filesystem->exists($testPath);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->components->twoColumnDetail('Yazma', $writeOk ? 'OK' : 'HATA');
        $this->components->twoColumnDetail('Okuma', $readOk ? 'OK' : 'HATA');
        $this->components->twoColumnDetail('Silme', $deleteOk ? 'OK' : 'HATA');

        if (! $writeOk || ! $readOk || ! $deleteOk) {
            $this->components->error('Drive sağlığı başarısız oldu. Logları kontrol edin.');

            return self::FAILURE;
        }

        $this->components->success('Drive sağlığı başarılı.');

        return self::SUCCESS;
    }
}
