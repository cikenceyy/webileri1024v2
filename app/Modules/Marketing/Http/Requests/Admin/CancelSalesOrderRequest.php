<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Marketing\Domain\Models\SalesOrder;
use Illuminate\Foundation\Http\FormRequest;

class CancelSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SalesOrder $order */
        $order = $this->route('order');

        return $order && ! $order->isClosed() && ($this->user()?->can('cancel', $order) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
