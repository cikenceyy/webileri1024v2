<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Domain\Models\BankAccount;

class BankAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'finance.bank.view');
    }

    public function view(User $user, BankAccount $account): bool
    {
        return $this->sameCompany($user, $account) && $this->hasPermission($user, 'finance.bank.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'finance.bank.create');
    }

    public function update(User $user, BankAccount $account): bool
    {
        return $this->sameCompany($user, $account) && $this->hasPermission($user, 'finance.bank.update');
    }

    public function delete(User $user, BankAccount $account): bool
    {
        return $this->sameCompany($user, $account) && $this->hasPermission($user, 'finance.bank.delete');
    }

    protected function sameCompany(User $user, BankAccount $account): bool
    {
        return (int) $user->company_id === (int) $account->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
