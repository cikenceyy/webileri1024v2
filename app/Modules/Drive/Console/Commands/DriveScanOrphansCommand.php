<?php

namespace App\Modules\Drive\Console\Commands;

use App\Modules\Drive\Domain\DriveStorage;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Console\Command;

class DriveScanOrphansCommand extends Command
{
    protected $signature = 'drive:scan-orphans {--disk= : Farklı bir disk adı kullan}';

    protected $description = 'Diskte olup veritabanında bulunmayan dosyaları ve tam tersini raporlar.';

    public function handle(DriveStorage $storage): int
    {
        $disk = $this->option('disk') ?: $storage->diskName();
        $filesystem = $storage->filesystem($disk);
        $prefix = trim(str_replace('{company_id}', '', config('drive.path_prefix', 'companies/{company_id}/drive')), '/');

        $this->components->info("Disk taranıyor: {$disk}");

        $dbPaths = Media::query()
            ->where('disk', $disk)
            ->pluck('path', 'id');

        $missing = [];
        foreach ($dbPaths as $id => $path) {
            if (! $filesystem->exists($path)) {
                $missing[$id] = $path;
            }
        }

        try {
            $diskPaths = collect($filesystem->allFiles($prefix));
        } catch (\Throwable $exception) {
            report($exception);
            $diskPaths = collect();
        }
        $orphans = $diskPaths->diff($dbPaths->values());

        $this->components->twoColumnDetail('DB Kayıtları', (string) $dbPaths->count());
        $this->components->twoColumnDetail('Disk Dosyaları', (string) $diskPaths->count());
        $this->components->twoColumnDetail('Diskte Eksik', (string) count($missing));
        $this->components->twoColumnDetail('Diskte Fazla', (string) $orphans->count());

        if ($missing) {
            $this->newLine();
            $this->components->error('Eksik dosyalar:');
            foreach ($missing as $id => $path) {
                $this->line(" - [#{$id}] {$path}");
            }
        }

        if ($orphans->isNotEmpty()) {
            $this->newLine();
            $this->components->warn('Orphan dosyalar:');
            foreach ($orphans as $path) {
                $this->line(" - {$path}");
            }
        }

        return ($missing || $orphans->isNotEmpty()) ? self::FAILURE : self::SUCCESS;
    }
}
