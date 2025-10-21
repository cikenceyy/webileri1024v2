<?php

namespace App\Core\Support\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CloudPostdeployCommand extends Command
{
    protected $signature = 'webileri:cloud:postdeploy';

    protected $description = 'Deploy sonrası cache durumunu ve temel sağlık kontrollerini raporlar.';

    public function handle(): int
    {
        $this->components->info('Cache dosyaları kontrol ediliyor...');

        $summary = [
            'config_cache' => file_exists(base_path('bootstrap/cache/config.php')),
            'route_cache' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'view_cache' => is_dir(storage_path('framework/views')),
            'event_cache' => file_exists(base_path('bootstrap/cache/events.php')),
        ];

        foreach ($summary as $label => $state) {
            $this->components->twoColumnDetail(Str::headline($label), $state ? 'aktif' : 'yok');
        }

        $cacheKey = 'cloud:postdeploy:ping';
        Cache::put($cacheKey, now()->toIso8601String(), 60);
        $this->components->twoColumnDetail('Cache yazma testi', Cache::has($cacheKey) ? 'ok' : 'fail');

        $this->components->info(sprintf('Toplam kayıtlı rota sayısı: %d', count(Route::getRoutes())));
        $this->components->success('Postdeploy kontrolü tamamlandı.');

        return self::SUCCESS;
    }
}
