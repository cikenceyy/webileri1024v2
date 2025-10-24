<?php

namespace App\Modules\Finance\Policies;

use App\Core\Access\Policies\CompanyOwnedPolicy;
use App\Models\User;
use App\Modules\Finance\Domain\Models\Invoice;

class InvoicePolicy extends CompanyOwnedPolicy
{
    protected string $permissionPrefix = 'finance.invoices';

    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionKey('view'));
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $invoice->isDraft() && $this->allows($user, $invoice, 'update');
    }

    public function issue(User $user, Invoice $invoice): bool
    {
        return $invoice->isDraft() && $user->can($this->permissionKey('issue')) && $this->belongsToCompany($user, $invoice);
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $invoice->isIssued() && $user->can($this->permissionKey('cancel')) && $this->belongsToCompany($user, $invoice);
    }

    public function print(User $user, Invoice $invoice): bool
    {
        return $this->allows($user, $invoice, 'print');
    }
}
