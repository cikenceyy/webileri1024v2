<?php

namespace App\Core\Tenancy\Jobs;

use App\Core\Support\Models\Company;
use App\Core\Support\Models\CompanyDomain;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Yeni kiracı kurulumunda root kullanıcıyı ve gerekli rolü idempotent şekilde oluşturur.
 */
class TenantProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $companyId,
        public string $email,
        public string $password,
        public string $role = 'superadmin',
        public ?string $devDomain = null,
    ) {
    }

    public function handle(PermissionRegistrar $registrar): void
    {
        $company = Company::query()->find($this->companyId);

        if (! $company) {
            return;
        }

        if (! class_exists(Role::class)) {
            return;
        }

        $registrar->setPermissionsTeamId($company->id);

        $role = Role::findOrCreate($this->role, 'web');

        $user = User::query()->firstOrNew([
            'company_id' => $company->id,
            'email' => $this->email,
        ]);

        if (! $user->exists) {
            $user->name = Arr::first(explode('@', $this->email)) ?: 'Root User';
        }

        $user->password = $this->password;
        $user->save();

        $user->syncRoles([$role->name]);

        if ($this->devDomain && app()->environment('local')) {
            CompanyDomain::query()->firstOrCreate([
                'host' => $this->devDomain,
            ], [
                'company_id' => $company->id,
                'is_primary' => false,
            ]);
        }

        try {
            Log::channel('cache')->info('tenancy.user.provision', [
                'company_id' => $company->id,
                'email' => $this->email,
                'role' => $role->name,
                'dev_domain' => $this->devDomain,
            ]);
        } catch (\Throwable) {
            // log kanalı kapalı olabilir
        }
    }
}
