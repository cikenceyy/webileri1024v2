<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\StockTransfer;

class StockTransferPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.transfer.view');
    }

    public function view(User $user, StockTransfer $transfer): bool
    {
        return $this->sameCompany($user, $transfer) && $this->hasPermission($user, 'inventory.transfer.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.transfer.create');
    }

    public function update(User $user, StockTransfer $transfer): bool
    {
        return $this->sameCompany($user, $transfer) && $this->hasPermission($user, 'inventory.transfer.update');
    }

    public function post(User $user, StockTransfer $transfer): bool
    {
        return $this->sameCompany($user, $transfer) && $this->hasPermission($user, 'inventory.transfer.post');
    }

    protected function sameCompany(User $user, StockTransfer $transfer): bool
    {
        return (int) $user->company_id === (int) $transfer->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
