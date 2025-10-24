<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\StockCount;

class StockCountPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.count.view');
    }

    public function view(User $user, StockCount $count): bool
    {
        return $this->sameCompany($user, $count) && $this->hasPermission($user, 'inventory.count.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.count.create');
    }

    public function update(User $user, StockCount $count): bool
    {
        return $this->sameCompany($user, $count) && $this->hasPermission($user, 'inventory.count.update');
    }

    public function reconcile(User $user, StockCount $count): bool
    {
        return $this->sameCompany($user, $count) && $this->hasPermission($user, 'inventory.count.reconcile');
    }

    protected function sameCompany(User $user, StockCount $count): bool
    {
        return (int) $user->company_id === (int) $count->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
