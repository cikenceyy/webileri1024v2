<?php

namespace App\Modules\HR\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\HR\Domain\Models\Employee;

class EmployeePolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'hr.employees';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function archive(User $user, Employee $employee): bool
    {
        return $employee->is_active && $this->allows($user, $employee, 'archive');
    }
}
