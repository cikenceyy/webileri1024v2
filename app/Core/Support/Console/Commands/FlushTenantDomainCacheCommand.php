<?php

namespace App\Core\Support\Console\Commands;

use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use Illuminate\Console\Command;

class FlushTenantDomainCacheCommand extends Command
{
    protected $signature = 'tenancy:flush-domain-cache {domain?}';

    protected $description = 'Clear cached tenant domain lookups.';

    public function handle(): int
    {
        $domain = $this->argument('domain');

        if ($domain) {
            IdentifyTenant::flushCacheForDomains($domain);
            $this->info("Önbellek temizlendi: {$domain}");

            return self::SUCCESS;
        }

        $domains = Company::query()->pluck('domain')->filter()->all();
        $aliases = CompanyDomain::query()->pluck('domain')->filter()->all();
        $all = array_unique(array_merge($domains, $aliases));

        if ($all === []) {
            $this->info('Temizlenecek domain bulunamadı.');

            return self::SUCCESS;
        }

        IdentifyTenant::flushCacheForDomains(...$all);
        $this->info('Tüm kiracı domain önbellekleri temizlendi.');

        return self::SUCCESS;
    }
}
