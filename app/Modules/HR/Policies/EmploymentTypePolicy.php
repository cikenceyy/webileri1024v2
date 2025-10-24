<?php

namespace App\Modules\HR\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\HR\Domain\Models\EmploymentType;

class EmploymentTypePolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'hr.employment_types';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }
}
