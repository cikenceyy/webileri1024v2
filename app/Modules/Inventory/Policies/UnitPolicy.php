<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\Unit;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.unit.view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->sameCompany($user, $unit) && $this->hasPermission($user, 'inventory.unit.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.unit.create');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->sameCompany($user, $unit) && $this->hasPermission($user, 'inventory.unit.update');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->sameCompany($user, $unit) && $this->hasPermission($user, 'inventory.unit.delete');
    }

    protected function sameCompany(User $user, Unit $unit): bool
    {
        return (int) $user->company_id === (int) $unit->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
