<?php

namespace App\Modules\Logistics\Policies;

use App\Models\User;
use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Auth\Access\Response;

class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'logistics.shipments.view');
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $this->sameCompany($user, $shipment)
            && $this->hasPermission($user, 'logistics.shipments.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'logistics.shipments.create');
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $this->sameCompany($user, $shipment)
            && $this->hasPermission($user, 'logistics.shipments.update');
    }

    public function pick(User $user, Shipment $shipment): Response
    {
        return $this->guard($user, $shipment, 'logistics.shipments.pick');
    }

    public function pack(User $user, Shipment $shipment): Response
    {
        return $this->guard($user, $shipment, 'logistics.shipments.pack');
    }

    public function ship(User $user, Shipment $shipment): Response
    {
        return $this->guard($user, $shipment, 'logistics.shipments.ship');
    }

    public function close(User $user, Shipment $shipment): Response
    {
        return $this->guard($user, $shipment, 'logistics.shipments.close');
    }

    public function cancel(User $user, Shipment $shipment): Response
    {
        return $this->guard($user, $shipment, 'logistics.shipments.cancel');
    }

    public function print(User $user, Shipment $shipment): Response
    {
        return $this->guard($user, $shipment, 'logistics.shipments.print');
    }

    protected function guard(User $user, Shipment $shipment, string $permission): Response
    {
        if (! $this->sameCompany($user, $shipment)) {
            return Response::deny(__('You cannot manage shipments for another company.'));
        }

        if (! $this->hasPermission($user, $permission)) {
            return Response::deny(__('You are not allowed to perform this action.'));
        }

        return Response::allow();
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
