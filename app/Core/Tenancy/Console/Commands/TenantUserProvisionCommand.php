<?php

namespace App\Core\Tenancy\Console\Commands;

use App\Core\Tenancy\Jobs\TenantProvisionJob;
use Illuminate\Console\Command;

/**
 * Root kullanıcı oluşturma komutu (CLI üzerinden güvenli provision).
 */
class TenantUserProvisionCommand extends Command
{
    protected $signature = 'tenant:user:provision {--company= : Şirket kimliği} {--email=rootuser@webileri.com} {--password=a1b2c3+} {--role=superadmin} {--dev-domain= : Opsiyonel geliştirme hostu}';

    protected $description = 'Belirtilen şirkette root kullanıcı oluşturur/günceller ve rol atamasını yapar.';

    public function handle(): int
    {
        $companyId = (int) $this->option('company');
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');
        $role = (string) $this->option('role');
        $devDomain = $this->option('dev-domain');

        if ($companyId <= 0) {
            $this->error('company parametresi zorunludur.');

            return self::FAILURE;
        }

        TenantProvisionJob::dispatchSync($companyId, $email, $password, $role, $devDomain ?: null);

        $this->info('Provision işlemi tamamlandı.');

        return self::SUCCESS;
    }
}
