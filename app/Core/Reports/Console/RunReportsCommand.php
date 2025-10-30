<?php

namespace App\Core\Reports\Console;

use App\Core\Reports\ReportRegistry;
use App\Core\Reports\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tanımlı cold raporları belirli şirketler için yeniden üretir.
 */
class RunReportsCommand extends Command
{
    protected $signature = 'reports:run {--report=} {--company=}';

    protected $description = 'Cold rapor snapshotlarını yeniler.';

    public function __construct(private readonly ReportRegistry $registry, private readonly ReportService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $reportKey = $this->option('report');
        $companyId = $this->option('company');

        $definitions = $reportKey ? [$this->registry->find($reportKey)] : $this->registry->all();
        $companyIds = $companyId ? [(int) $companyId] : $this->resolveCompanyIds();

        foreach ($definitions as $definition) {
            foreach ($companyIds as $id) {
                if (! $this->registry->shouldGenerate($id, $definition)) {
                    $this->line("Atlanıyor: {$definition['key']} (firma {$id})");
                    continue;
                }

                $snapshot = $this->service->dispatch($id, $definition['key'], []);
                $this->info("Rapor kuyruğa alındı: {$definition['key']} (firma {$id}) snapshot #{$snapshot->id}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function resolveCompanyIds(): array
    {
        return DB::table('companies')->pluck('id')->map(fn ($id) => (int) $id)->all();
    }
}
