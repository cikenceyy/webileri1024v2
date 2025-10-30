<?php

namespace App\Core\Tenancy\Console\Commands;

use App\Core\Support\Models\CompanyDomain;
use App\Core\Tenancy\Support\DomainNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Bir domaini şirketten kaldırmak için CLI komutu.
 */
class TenantDomainRemoveCommand extends Command
{
    protected $signature = 'tenant:domain:remove {--company= : Şirket kimliği} {--host= : Silinecek host}';

    protected $description = 'Şirkete ait domain kaydını siler (cache otomatik temizlenir).';

    public function handle(): int
    {
        $companyId = (int) $this->option('company');
        $hostInput = (string) $this->option('host');

        if ($companyId <= 0 || $hostInput === '') {
            $this->error('company ve host seçenekleri zorunludur.');

            return self::FAILURE;
        }

        $normalizedHost = DomainNormalizer::normalize($hostInput);
        $domain = CompanyDomain::query()
            ->where('company_id', $companyId)
            ->where('host', $normalizedHost)
            ->first();

        if (! $domain) {
            $this->error('Belirtilen host bu şirkete ait değil.');

            return self::FAILURE;
        }

        $domainId = $domain->id;
        $domain->delete();

        $this->info(sprintf('%s hostu silindi.', $normalizedHost));

        try {
            Log::channel('cache')->info('tenancy.domain.remove', [
                'company_id' => $companyId,
                'host' => $normalizedHost,
                'domain_id' => $domainId,
            ]);
        } catch (\Throwable) {
            // log kanalı devre dışı olabilir.
        }

        return self::SUCCESS;
    }
}
