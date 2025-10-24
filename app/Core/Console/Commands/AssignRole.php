<?php

namespace App\Core\Console\Commands;

use App\Core\Support\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssignRole extends Command
{
    protected $signature = 'acl:assign {company_id} {user_id} {role}';

    protected $description = 'Belirtilen şirkette kullanıcıya rol atar';

    public function handle(PermissionRegistrar $registrar): int
    {
        $companyId = (int) $this->argument('company_id');
        $userId = (int) $this->argument('user_id');
        $roleName = (string) $this->argument('role');

        $company = Company::query()->find($companyId);
        if (! $company) {
            $this->error('Şirket bulunamadı.');

            return self::FAILURE;
        }

        /** @var User|null $user */
        $user = User::query()->where('company_id', $companyId)->find($userId);
        if (! $user) {
            $this->error('Kullanıcı bulunamadı veya şirkete ait değil.');

            return self::FAILURE;
        }

        $role = Role::query()->where('name', $roleName)->first();
        if (! $role) {
            $this->error('Rol bulunamadı: ' . $roleName);

            return self::FAILURE;
        }

        $registrar->setPermissionsTeamId($companyId);

        $user->syncRoles([$role]);

        $this->info(sprintf('Kullanıcı %s için %s rolü atandı.', $user->name ?? $user->email, $roleName));

        return self::SUCCESS;
    }
}
