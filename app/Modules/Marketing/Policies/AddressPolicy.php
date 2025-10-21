<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\CustomerAddress;

class AddressPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.address';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, CustomerAddress $address): bool
    {
        return parent::view($user, $address);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, CustomerAddress $address): bool
    {
        return parent::update($user, $address);
    }

    public function delete(User $user, CustomerAddress $address): bool
    {
        return parent::delete($user, $address);
    }
}
