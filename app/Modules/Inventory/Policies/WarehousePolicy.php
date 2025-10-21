<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.warehouse.view');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $this->sameCompany($user, $warehouse) && $this->hasPermission($user, 'inventory.warehouse.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.warehouse.create');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $this->sameCompany($user, $warehouse) && $this->hasPermission($user, 'inventory.warehouse.update');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $this->sameCompany($user, $warehouse) && $this->hasPermission($user, 'inventory.warehouse.delete');
    }

    protected function sameCompany(User $user, Warehouse $warehouse): bool
    {
        return (int) $user->company_id === (int) $warehouse->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
