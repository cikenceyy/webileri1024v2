<?php

namespace App\Modules\HR\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\HR\Domain\Models\Title;

class TitlePolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'hr.titles';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }
}
