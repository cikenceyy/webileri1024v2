<?php

namespace App\Core\Tenancy\Console\Commands;

use App\Core\Support\Models\Company;
use App\Core\Tenancy\DomainCacheManager;
use Illuminate\Console\Command;

/**
 * Domain cacheini manuel temizlemek için CLI komutu.
 */
class TenantFlushDomainCacheCommand extends Command
{
    protected $signature = 'tenant:flush-domain-cache {--company= : Şirket kimliği}';

    protected $description = 'Belirtilen şirket için domain cacheini temizler.';

    public function __construct(private readonly DomainCacheManager $cacheManager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $companyId = (int) $this->option('company');

        if ($companyId <= 0) {
            $this->error('company parametresi zorunludur.');

            return self::FAILURE;
        }

        $company = Company::query()->find($companyId);

        if (! $company) {
            $this->error('Şirket bulunamadı.');

            return self::FAILURE;
        }

        $this->cacheManager->flushForCompany($companyId, context: [
            'reason' => 'cli.domain.flush',
        ]);

        $this->info(sprintf('%s şirketi için domain cache temizlendi.', $company->name));

        return self::SUCCESS;
    }
}
