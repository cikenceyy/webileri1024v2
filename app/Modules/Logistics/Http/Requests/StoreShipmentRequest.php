<?php

namespace App\Modules\Logistics\Http\Requests;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Shipment::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->attributes->get('company_id')
            ?? (app()->bound('company') ? app('company')->id : null);

        return [
            'shipment_no' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('shipments', 'shipment_no')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'ship_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'preparing', 'in_transit', 'delivered', 'cancelled'])],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId)->whereNull('deleted_at');
                }),
            ],
            'order_id' => [
                'nullable',
                Rule::exists('orders', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId)->whereNull('deleted_at');
                }),
            ],
            'carrier' => ['nullable', 'string', 'max:64'],
            'tracking_no' => ['nullable', 'string', 'max:64'],
            'package_count' => ['nullable', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0'],
            'volume_dm3' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
