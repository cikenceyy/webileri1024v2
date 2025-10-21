<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.product.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->sameCompany($user, $product) && $this->hasPermission($user, 'inventory.product.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.product.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->sameCompany($user, $product) && $this->hasPermission($user, 'inventory.product.update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->sameCompany($user, $product) && $this->hasPermission($user, 'inventory.product.delete');
    }

    public function attachMedia(User $user, Product $product): bool
    {
        return $this->sameCompany($user, $product) && $this->hasPermission($user, 'inventory.product.attach_media');
    }

    protected function sameCompany(User $user, Product $product): bool
    {
        return (int) $user->company_id === (int) $product->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
