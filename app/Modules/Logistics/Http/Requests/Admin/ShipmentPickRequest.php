<?php

namespace App\Modules\Logistics\Http\Requests\Admin;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentPickRequest extends FormRequest
{
    protected Shipment $shipment;

    public function authorize(): bool
    {
        /** @var Shipment|null $shipment */
        $shipment = $this->route('shipment');
        $this->shipment = $shipment ?? new Shipment();

        return $shipment !== null && $this->user()?->can('pick', $shipment);
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();
        $shipmentId = $this->shipment->id ?? 0;

        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => [
                'required',
                'integer',
                Rule::exists('shipment_lines', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('shipment_id', $shipmentId)),
            ],
            'lines.*.picked_qty' => ['required', 'numeric', 'min:0'],
            'lines.*.warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines.*.bin_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_bins', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $lines = $this->shipment->lines()->get()->keyBy('id');

            foreach ($this->input('lines', []) as $payload) {
                $lineId = $payload['id'] ?? null;
                $picked = (float) ($payload['picked_qty'] ?? 0);

                if ($lineId && $lines->has($lineId)) {
                    $line = $lines->get($lineId);
                    if ($picked > (float) $line->qty) {
                        $validator->errors()->add("lines.{$lineId}.picked_qty", __('Picked quantity cannot exceed ordered quantity.'));
                    }
                }
            }
        });
    }
}
