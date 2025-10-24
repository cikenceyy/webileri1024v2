<?php

namespace App\Modules\Production\Http\Requests\Admin;

use App\Modules\Production\Domain\Models\WorkOrder;

class WorkOrderUpdateRequest extends WorkOrderStoreRequest
{
    public function authorize(): bool
    {
        /** @var WorkOrder|null $workOrder */
        $workOrder = $this->route('workOrder');

        return $workOrder ? ($this->user()?->can('update', $workOrder) ?? false) : false;
    }
}
