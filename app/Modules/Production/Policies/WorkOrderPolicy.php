<?php

namespace App\Modules\Production\Policies;

use App\Models\User;
use App\Modules\Production\Domain\Models\WorkOrder;
use Illuminate\Auth\Access\Response;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'production.workorders.view');
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $this->sameCompany($user, $workOrder)
            && $this->hasPermission($user, 'production.workorders.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'production.workorders.create');
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $this->sameCompany($user, $workOrder)
            && $this->hasPermission($user, 'production.workorders.update');
    }

    public function release(User $user, WorkOrder $workOrder): Response
    {
        return $this->guard($user, $workOrder, 'production.workorders.release');
    }

    public function start(User $user, WorkOrder $workOrder): Response
    {
        return $this->guard($user, $workOrder, 'production.workorders.start');
    }

    public function issue(User $user, WorkOrder $workOrder): Response
    {
        return $this->guard($user, $workOrder, 'production.workorders.issue');
    }

    public function complete(User $user, WorkOrder $workOrder): Response
    {
        return $this->guard($user, $workOrder, 'production.workorders.complete');
    }

    public function close(User $user, WorkOrder $workOrder): Response
    {
        return $this->guard($user, $workOrder, 'production.workorders.close');
    }

    public function cancel(User $user, WorkOrder $workOrder): Response
    {
        return $this->guard($user, $workOrder, 'production.workorders.cancel');
    }

    protected function guard(User $user, WorkOrder $workOrder, string $permission): Response
    {
        if (! $this->sameCompany($user, $workOrder)) {
            return Response::deny(__('You cannot manage work orders for another company.'));
        }

        if (! $this->hasPermission($user, $permission)) {
            return Response::deny(__('You are not allowed to perform this action.'));
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
