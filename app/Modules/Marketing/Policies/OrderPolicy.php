<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\SalesOrder;

class OrderPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.orders';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, SalesOrder $order): bool
    {
        return $this->allows($user, $order, 'view');
    }

    public function create(User $user): bool
    {
        if ($this->isAccountant($user) || $this->isIntern($user)) {
            return false;
        }

        return parent::create($user);
    }

    public function update(User $user, SalesOrder $order): bool
    {
        if ($this->isIntern($user)) {
            return false;
        }

        return $order->isDraft() && $this->allows($user, $order, 'update');
    }

    public function confirm(User $user, SalesOrder $order): bool
    {
        if ($this->isIntern($user)) {
            return false;
        }

        return $order->isDraft() && $this->allows($user, $order, 'confirm');
    }

    public function cancel(User $user, SalesOrder $order): bool
    {
        if ($this->isIntern($user)) {
            return false;
        }

        return ! $order->isClosed() && $this->allows($user, $order, 'cancel');
    }

    protected function isAccountant(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('accountant');
    }

    protected function isIntern(User $user): bool
    {
        return method_exists($user, 'hasRole') && $user->hasRole('intern');
    }
}
