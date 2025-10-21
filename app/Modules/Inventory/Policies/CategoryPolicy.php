<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;
use App\Modules\Inventory\Domain\Models\ProductCategory;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.category.view');
    }

    public function view(User $user, ProductCategory $category): bool
    {
        return $this->sameCompany($user, $category) && $this->hasPermission($user, 'inventory.category.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'inventory.category.create');
    }

    public function update(User $user, ProductCategory $category): bool
    {
        return $this->sameCompany($user, $category) && $this->hasPermission($user, 'inventory.category.update');
    }

    public function delete(User $user, ProductCategory $category): bool
    {
        return $this->sameCompany($user, $category) && $this->hasPermission($user, 'inventory.category.delete');
    }

    protected function sameCompany(User $user, ProductCategory $category): bool
    {
        return (int) $user->company_id === (int) $category->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
