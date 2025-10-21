<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Activity;

class ActivityPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.activity';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Activity $activity): bool
    {
        return parent::view($user, $activity);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, Activity $activity): bool
    {
        return parent::update($user, $activity);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return parent::delete($user, $activity);
    }
}
