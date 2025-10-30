<?php

namespace App\Core\Tenancy\Console\Commands;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Core\Tenancy\Support\DomainNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * CLI üzerinden şirkete yeni domain eklemek için kullanılır (sadece superadmin için).
 */
class TenantDomainAddCommand extends Command
{
    protected $signature = 'tenant:domain:add {--company= : Şirket kimliği} {--host= : Eklenecek domain/host} {--primary : Birincil domain olarak işaretle}';

    protected $description = 'Belirtilen şirkete domain ekler (cache otomatik temizlenir).';

    public function handle(): int
    {
        $companyId = (int) $this->option('company');
        $hostInput = (string) $this->option('host');
        $primary = (bool) $this->option('primary');

        if ($companyId <= 0 || $hostInput === '') {
            $this->error('company ve host parametreleri zorunludur.');

            return self::FAILURE;
        }

        $company = Company::query()->find($companyId);
        if (! $company) {
            $this->error('Şirket bulunamadı.');

            return self::FAILURE;
        }

        $normalizedHost = DomainNormalizer::normalize($hostInput);

        if ($normalizedHost === '') {
            $this->error('Host değeri geçersiz.');

            return self::FAILURE;
        }

        $existing = CompanyDomain::query()->where('host', $normalizedHost)->first();
        if ($existing && (int) $existing->company_id !== $companyId) {
            $this->error('Host başka bir şirkete kayıtlı.');

            return self::FAILURE;
        }

        $domain = CompanyDomain::query()->updateOrCreate(
            ['host' => $normalizedHost],
            [
                'company_id' => $companyId,
                'is_primary' => $primary,
            ]
        );

        $this->info(sprintf('%s için %s hostu kaydedildi (%s).', $company->name, $normalizedHost, $primary ? 'primary' : 'alias'));

        try {
            Log::channel('cache')->info('tenancy.domain.add', [
                'company_id' => $companyId,
                'host' => $normalizedHost,
                'is_primary' => $primary,
                'domain_id' => $domain->id,
            ]);
        } catch (\Throwable) {
            // log kanalı devre dışı olabilir.
        }

        return self::SUCCESS;
    }
}
