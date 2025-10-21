<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Opportunity;

class OpportunityPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.opportunity';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        return parent::view($user, $opportunity);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        return parent::update($user, $opportunity);
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return parent::delete($user, $opportunity);
    }
}
