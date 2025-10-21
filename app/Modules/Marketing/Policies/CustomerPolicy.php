<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Customer;

class CustomerPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.customer';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Customer $customer): bool
    {
        return parent::view($user, $customer);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, Customer $customer): bool
    {
        return parent::update($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return parent::delete($user, $customer);
    }

    public function import(User $user): bool
    {
        return $user->can($this->permissionKey('import'));
    }
}
