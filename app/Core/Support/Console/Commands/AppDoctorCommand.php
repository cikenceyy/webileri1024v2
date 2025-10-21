<?php

namespace App\Core\Support\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppDoctorCommand extends Command
{
    protected $signature = 'app:doctor {--optimize : Config, route ve view cache işlemlerini çalıştırır}';

    protected $description = 'Temel kurulum kontrollerini gerçekleştirir.';

    public function handle(): int
    {
        $rows = [];

        $rows[] = $this->check('Environment dosyası mevcut', file_exists(base_path('.env')));
        $rows[] = $this->check('APP_KEY tanımlı', ! empty(config('app.key')));

        $queueDriver = config('queue.default');
        $rows[] = $this->check(
            'Queue sürücüsü (sync dışında önerilir)',
            $queueDriver !== 'sync',
            $queueDriver
        );

        $storageLink = public_path('storage');
        $rows[] = $this->check(
            'storage link mevcut',
            is_link($storageLink) || file_exists($storageLink),
            $storageLink
        );

        $rows[] = $this->check(
            'Config cache',
            file_exists(base_path('bootstrap/cache/config.php')),
            'php artisan config:cache'
        );

        $routeCache = collect(glob(base_path('bootstrap/cache/routes-*.php') ?: []))->isNotEmpty();
        $rows[] = $this->check(
            'Route cache',
            $routeCache,
            'php artisan route:cache'
        );

        $rows[] = $this->check(
            'View cache dizini yazılabilir',
            is_writable(storage_path('framework/views')),
            storage_path('framework/views')
        );

        $this->table(['Kontrol', 'Durum', 'Not'], $rows);

        if ($this->option('optimize')) {
            $this->components->info('Optimize komutları çalıştırılıyor...');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $this->components->info('Önbellek işlemleri tamamlandı.');
        }

        return collect($rows)->contains(fn (array $row) => $row[1] === 'FAIL')
            ? Command::FAILURE
            : Command::SUCCESS;
    }

    protected function check(string $label, bool $passed, ?string $note = null): array
    {
        return [
            $label,
            $passed ? 'OK' : 'FAIL',
            $passed ? ($note ?? '-') : ($note ?? 'Manuel müdahale gerekli'),
        ];
    }
}
