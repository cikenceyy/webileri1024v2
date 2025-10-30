<?php

namespace App\Core\Tenancy\Console\Commands;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use Illuminate\Console\Command;

/**
 * Şirkete bağlı domain listesini okunabilir tablo olarak gösterir.
 */
class TenantDomainListCommand extends Command
{
    protected $signature = 'tenant:domain:list {--company= : Şirket kimliği}';

    protected $description = 'Belirtilen şirketin domain kayıtlarını listeler.';

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

        $domains = CompanyDomain::query()
            ->where('company_id', $companyId)
            ->orderByDesc('is_primary')
            ->orderBy('host')
            ->get(['id', 'host', 'is_primary', 'created_at', 'updated_at']);

        if ($domains->isEmpty()) {
            $this->warn('Kayıtlı domain bulunamadı.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Host', 'Tür', 'Oluşturma', 'Güncelleme'],
            $domains->map(fn ($domain) => [
                $domain->id,
                $domain->host,
                $domain->is_primary ? 'Primary' : 'Alias',
                optional($domain->created_at)->toDateTimeString(),
                optional($domain->updated_at)->toDateTimeString(),
            ])->toArray()
        );

        return self::SUCCESS;
    }
}
