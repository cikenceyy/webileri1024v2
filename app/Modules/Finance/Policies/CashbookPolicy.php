<?php

namespace App\Modules\Finance\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Finance\Domain\Models\CashbookEntry;

class CashbookPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'finance.cashbook';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }
}
