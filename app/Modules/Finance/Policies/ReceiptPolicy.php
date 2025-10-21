<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Domain\Models\Receipt;

class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'finance.receipt.view');
    }

    public function view(User $user, Receipt $receipt): bool
    {
        return $this->sameCompany($user, $receipt) && $this->hasPermission($user, 'finance.receipt.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'finance.receipt.create');
    }

    public function update(User $user, Receipt $receipt): bool
    {
        return $this->sameCompany($user, $receipt) && $this->hasPermission($user, 'finance.receipt.update');
    }

    public function delete(User $user, Receipt $receipt): bool
    {
        return $this->sameCompany($user, $receipt) && $this->hasPermission($user, 'finance.receipt.delete');
    }

    protected function sameCompany(User $user, Receipt $receipt): bool
    {
        return (int) $user->company_id === (int) $receipt->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
