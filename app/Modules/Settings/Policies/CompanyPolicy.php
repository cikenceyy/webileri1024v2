<?php

namespace App\Modules\Settings\Policies;

use App\Core\Support\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $this->sameCompany($user, $company) && $this->hasPermission($user, 'settings.company.view');
    }

    public function update(User $user, Company $company): bool
    {
        return $this->sameCompany($user, $company) && $this->hasPermission($user, 'settings.company.update');
    }

    protected function sameCompany(User $user, Company $company): bool
    {
        return (int) $user->company_id === (int) $company->id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
