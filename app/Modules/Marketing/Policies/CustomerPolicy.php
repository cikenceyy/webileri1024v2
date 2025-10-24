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
        return $user->can($this->permissionKey('view'));
    }
}
