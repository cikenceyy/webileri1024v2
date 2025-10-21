<?php

namespace App\Modules\Production\Policies;

use App\Models\User;
use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Auth\Access\Response;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'production.workorder.view');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $this->sameCompany($user, $workOrder)
            && $this->hasPermission($user, 'production.workorder.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'production.workorder.create');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $this->sameCompany($user, $workOrder)
            && $this->hasPermission($user, 'production.workorder.update');
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $this->sameCompany($user, $workOrder)
            && $this->hasPermission($user, 'production.workorder.delete');
    }

    public function close(User $user, WorkOrder $workOrder): Response
    {
        if (! $this->sameCompany($user, $workOrder)) {
            return Response::deny(__('You cannot close work orders for another company.'));
        }

        if (! $this->hasPermission($user, 'production.workorder.close')) {
            return Response::deny(__('You are not allowed to close work orders.'));
        }

        if ($workOrder->status === 'done') {
            return Response::deny(__('The work order is already marked as done.'));
        }

        return Response::allow();
    }

    protected function sameCompany(User $user, WorkOrder $workOrder): bool
    {
        return (int) $user->company_id === (int) $workOrder->company_id;
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo') || ! class_exists(\Spatie\Permission\Models\Permission::class)) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }
}
