<?php

namespace App\Modules\Settings\Policies;

use App\Core\Support\Models\CompanyDomain;
use App\Models\User;

class CompanyDomainPolicy
{
    public function create(User $user): bool
    {
        return $this->hasPermission($user);
    }

    public function update(User $user, CompanyDomain $domain): bool
    {
        return $this->sameCompany($user, $domain) && $this->hasPermission($user);
    }

    public function delete(User $user, CompanyDomain $domain): bool
    {
        return $this->sameCompany($user, $domain) && $this->hasPermission($user);
    }

    protected function sameCompany(User $user, CompanyDomain $domain): bool
    {
        return (int) $user->company_id === (int) $domain->company_id;
    }

    protected function hasPermission(User $user): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo('settings.company.domains.manage');
    }
}
