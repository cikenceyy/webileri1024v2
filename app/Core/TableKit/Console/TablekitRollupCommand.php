<?php

namespace App\Core\TableKit\Console;

use App\Core\TableKit\Services\MetricRollupService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * TableKit günlük metrik özetlerini oluşturmak için artisan komutu.
 * Amaç: Cron job ile çağrılarak rapor tablolarını güncel tutmak.
 */
class TablekitRollupCommand extends Command
{
    protected $signature = 'tablekit:rollup {--date=}';

    protected $description = 'TableKit metriklerini günlük özet tablosuna taşır.';

    public function __construct(private readonly MetricRollupService $rollupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dateOption = $this->option('date');
        $date = $dateOption ? CarbonImmutable::parse($dateOption) : CarbonImmutable::yesterday();

        $this->info('TableKit metrik rollup başlatılıyor: ' . $date->toDateString());

        $this->rollupService->rollupForDate($date);

        $this->info('TableKit metrik rollup tamamlandı.');

        return self::SUCCESS;
    }
}
