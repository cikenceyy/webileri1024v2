<?php

namespace App\Modules\Logistics\Policies;

use App\Models\User;
use App\Modules\Logistics\Domain\Models\Shipment;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'logistics.shipment.view');
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $this->sameCompany($user, $shipment)
            && $this->hasPermission($user, 'logistics.shipment.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'logistics.shipment.create');
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $this->sameCompany($user, $shipment)
            && $this->hasPermission($user, 'logistics.shipment.update');
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $this->sameCompany($user, $shipment)
            && $this->hasPermission($user, 'logistics.shipment.delete');
    }

    protected function sameCompany(User $user, Shipment $shipment): bool
    {
        return (int) $user->company_id === (int) $shipment->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
