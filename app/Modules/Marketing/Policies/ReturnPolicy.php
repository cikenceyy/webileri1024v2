<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\ReturnRequest;

class ReturnPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.returns';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, ReturnRequest $return): bool
    {
        return $this->allows($user, $return, 'view');
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function approve(User $user, ReturnRequest $return): bool
    {
        return $return->isOpen() && $this->allows($user, $return, 'approve');
    }

    public function close(User $user, ReturnRequest $return): bool
    {
        return $return->isApproved() && $this->allows($user, $return, 'close');
    }
}
