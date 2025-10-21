<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Marketing\Domain\Models\Quote;

class QuotePolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.quote';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, Quote $quote): bool
    {
        return parent::view($user, $quote);
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, Quote $quote): bool
    {
        return parent::update($user, $quote);
    }

    public function delete(User $user, Quote $quote): bool
    {
        return parent::delete($user, $quote);
    }
}
