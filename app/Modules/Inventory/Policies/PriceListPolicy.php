<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\PriceList;

class PriceListPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.pricelist.view');
    }

    public function view(User $user, PriceList $priceList): bool
    {
        return $this->sameCompany($user, $priceList) && $this->hasPermission($user, 'inventory.pricelist.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.pricelist.create');
    }

    public function update(User $user, PriceList $priceList): bool
    {
        return $this->sameCompany($user, $priceList) && $this->hasPermission($user, 'inventory.pricelist.update');
    }

    public function delete(User $user, PriceList $priceList): bool
    {
        return $this->sameCompany($user, $priceList) && $this->hasPermission($user, 'inventory.pricelist.delete');
    }

    protected function sameCompany(User $user, PriceList $priceList): bool
    {
        return (int) $user->company_id === (int) $priceList->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
