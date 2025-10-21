<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Domain\Models\Invoice;
use Illuminate\Auth\Access\Response;

class ArInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'finance.invoice.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->sameCompany($user, $invoice) && $this->hasPermission($user, 'finance.invoice.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'finance.invoice.create');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->sameCompany($user, $invoice) && $this->hasPermission($user, 'finance.invoice.update');
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $this->sameCompany($user, $invoice) && $this->hasPermission($user, 'finance.invoice.delete');
    }

    public function convertOrder(User $user): bool
    {
        return $this->hasPermission($user, 'finance.invoice.convert_order');
    }

    public function publish(User $user, Invoice $invoice): Response
    {
        if (! $this->sameCompany($user, $invoice)) {
            return Response::deny(__('You cannot publish invoices from another company.'));
        }

        if (! $this->hasPermission($user, 'finance.invoice.publish')) {
            return Response::deny(__('You are not allowed to publish invoices.'));
        }

        if ($invoice->status === 'published') {
            return Response::deny(__('The invoice is already published.'));
        }

        return Response::allow();
    }

    protected function sameCompany(User $user, Invoice $invoice): bool
    {
        return (int) $user->company_id === (int) $invoice->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
