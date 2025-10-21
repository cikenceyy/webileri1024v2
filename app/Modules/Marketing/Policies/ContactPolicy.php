<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\CustomerContact;

class ContactPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.contact';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, CustomerContact $contact): bool
    {
        return parent::view($user, $contact);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, CustomerContact $contact): bool
    {
        return parent::update($user, $contact);
    }

    public function delete(User $user, CustomerContact $contact): bool
    {
        return parent::delete($user, $contact);
    }
}
