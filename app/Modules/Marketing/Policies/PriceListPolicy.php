<?php

namespace App\Modules\Marketing\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Inventory\Domain\Models\PriceList;

class PriceListPolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'marketing.pricelists';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function view(User $user, PriceList $priceList): bool
    {
        return $this->allows($user, $priceList, 'view');
    }

    public function create(User $user): bool
    {
        return parent::create($user);
    }

    public function update(User $user, PriceList $priceList): bool
    {
        return $this->allows($user, $priceList, 'update');
    }

    public function delete(User $user, PriceList $priceList): bool
    {
        return $this->allows($user, $priceList, 'delete');
    }

    public function bulkUpdate(User $user, PriceList $priceList): bool
    {
        return $this->allows($user, $priceList, 'bulk_update');
    }
}
