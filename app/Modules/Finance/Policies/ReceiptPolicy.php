<?php

namespace App\Modules\Finance\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Finance\Domain\Models\Receipt;

class ReceiptPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'finance.receipts';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function apply(User $user, Receipt $receipt): bool
    {
        return $this->allows($user, $receipt, 'apply');
    }
}
