<?php

namespace App\Modules\Production\Policies;

use App\Models\User;
use App\Modules\Production\Domain\Models\Bom;

class BomPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'production.bom.view');
    }

    public function view(User $user, Bom $bom): bool
    {
        if ($bom->product && (int) $bom->product->company_id !== (int) $user->company_id) {
            return false;
        }

        return $this->hasPermission($user, 'production.bom.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'production.bom.create');
    }

    public function update(User $user, Bom $bom): bool
    {
        if ($bom->product && (int) $bom->product->company_id !== (int) $user->company_id) {
            return false;
        }

        return $this->hasPermission($user, 'production.bom.update');
    }

    public function delete(User $user, Bom $bom): bool
    {
        if ($bom->product && (int) $bom->product->company_id !== (int) $user->company_id) {
            return false;
        }

        return $this->hasPermission($user, 'production.bom.delete');
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
