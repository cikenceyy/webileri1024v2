<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;

class StockPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.stock.view');
    }

    public function move(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.stock.move');
    }

    public function adjust(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.stock.adjust');
    }

    public function transfer(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.stock.transfer');
    }

    public function viewReports(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.report.view');
    }

    public function reserve(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.stock.reserve');
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
