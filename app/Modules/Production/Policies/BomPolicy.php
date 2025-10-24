<?php

namespace App\Modules\Production\Policies;

use App\Models\User;
use App\Modules\Production\Domain\Models\Bom;

class BomPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'production.boms.view');
    }

    public function view(User $user, Bom $bom): bool
    {
        if ($bom->company_id && (int) $bom->company_id !== (int) $user->company_id) {
            return false;
        }

        return $this->hasPermission($user, 'production.boms.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'production.boms.create');
    }

    public function update(User $user, Bom $bom): bool
    {
        if ($bom->company_id && (int) $bom->company_id !== (int) $user->company_id) {
            return false;
        }

        return $this->hasPermission($user, 'production.boms.update');
    }

    public function delete(User $user, Bom $bom): bool
    {
        if ($bom->company_id && (int) $bom->company_id !== (int) $user->company_id) {
            return false;
        }

        return $this->hasPermission($user, 'production.boms.delete');
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
