<?php

namespace App\Modules\Logistics\Policies;

use App\Models\User;
use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use Illuminate\Auth\Access\Response;

class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'logistics.receipts.view');
    }

    public function view(User $user, GoodsReceipt $receipt): bool
    {
        return $this->sameCompany($user, $receipt)
            && $this->hasPermission($user, 'logistics.receipts.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'logistics.receipts.create');
    }

    public function update(User $user, GoodsReceipt $receipt): bool
    {
        return $this->sameCompany($user, $receipt)
            && $this->hasPermission($user, 'logistics.receipts.update');
    }

    public function receive(User $user, GoodsReceipt $receipt): Response
    {
        return $this->guard($user, $receipt, 'logistics.receipts.receive');
    }

    public function reconcile(User $user, GoodsReceipt $receipt): Response
    {
        return $this->guard($user, $receipt, 'logistics.receipts.reconcile');
    }

    public function close(User $user, GoodsReceipt $receipt): Response
    {
        return $this->guard($user, $receipt, 'logistics.receipts.close');
    }

    public function cancel(User $user, GoodsReceipt $receipt): Response
    {
        return $this->guard($user, $receipt, 'logistics.receipts.cancel');
    }

    public function print(User $user, GoodsReceipt $receipt): Response
    {
        return $this->guard($user, $receipt, 'logistics.receipts.print');
    }

    protected function guard(User $user, GoodsReceipt $receipt, string $permission): Response
    {
        if (! $this->sameCompany($user, $receipt)) {
            return Response::deny(__('You cannot manage receipts for another company.'));
        }

        if (! $this->hasPermission($user, $permission)) {
            return Response::deny(__('You are not allowed to perform this action.'));
        }

        return Response::allow();
    }

    protected function sameCompany(User $user, GoodsReceipt $receipt): bool
    {
        return (int) $user->company_id === (int) $receipt->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
