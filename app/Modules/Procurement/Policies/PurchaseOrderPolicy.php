<?php

namespace App\Modules\Procurement\Policies;

use App\Models\User;
use App\Modules\Procurement\Domain\Models\PurchaseOrder;
use Illuminate\Auth\Access\Response;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'procurement.purchase_order.view');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->sameCompany($user, $purchaseOrder)
            && $this->hasPermission($user, 'procurement.purchase_order.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'procurement.purchase_order.create');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->sameCompany($user, $purchaseOrder)
            && $this->hasPermission($user, 'procurement.purchase_order.update');
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->sameCompany($user, $purchaseOrder)
            && $this->hasPermission($user, 'procurement.purchase_order.delete');
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): Response
    {
        if (! $this->sameCompany($user, $purchaseOrder)) {
            return Response::deny(__('You cannot approve purchase orders for another company.'));
        }

        if (! $this->hasPermission($user, 'procurement.purchase_order.approve')) {
            return Response::deny(__('You are not allowed to approve purchase orders.'));
        }

        if ($purchaseOrder->status !== 'draft') {
            return Response::deny(__('Only draft purchase orders can be approved.'));
        }

        return Response::allow();
    }

    protected function sameCompany(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return (int) $user->company_id === (int) $purchaseOrder->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
