<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Customer;

class CustomerPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.customers';

    public function viewAny(User $user): bool
    {
        if ($this->restricted($user)) {
            return false;
        }

        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Customer $customer): bool
    {
        if ($this->restricted($user)) {
            return false;
        }

        return parent::view($user, $customer);
    }

    protected function restricted(User $user): bool
    {
        return method_exists($user, 'hasRole')
            && ($user->hasRole('accountant') || $user->hasRole('intern'));
    }
}
