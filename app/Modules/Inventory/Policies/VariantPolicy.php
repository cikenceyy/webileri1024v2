<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\ProductVariant;

class VariantPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.variant.view');
    }

    public function view(User $user, ProductVariant $variant): bool
    {
        return $this->sameCompany($user, $variant) && $this->hasPermission($user, 'inventory.variant.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.variant.create');
    }

    public function update(User $user, ProductVariant $variant): bool
    {
        return $this->sameCompany($user, $variant) && $this->hasPermission($user, 'inventory.variant.update');
    }

    public function delete(User $user, ProductVariant $variant): bool
    {
        return $this->sameCompany($user, $variant) && $this->hasPermission($user, 'inventory.variant.delete');
    }

    protected function sameCompany(User $user, ProductVariant $variant): bool
    {
        return (int) $user->company_id === (int) $variant->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
