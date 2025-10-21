<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Domain\Models\Allocation;

class AllocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'finance.allocation.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'finance.allocation.create');
    }

    public function delete(User $user, Allocation $allocation): bool
    {
        return $this->sameCompany($user, $allocation) && $this->hasPermission($user, 'finance.allocation.delete');
    }

    protected function sameCompany(User $user, Allocation $allocation): bool
    {
        return (int) $user->company_id === (int) $allocation->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
