<?php

namespace App\Modules\Logistics\Http\Requests\Admin;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentPackRequest extends FormRequest
{
    protected Shipment $shipment;

    public function authorize(): bool
    {
        /** @var Shipment|null $shipment */
        $shipment = $this->route('shipment');
        $this->shipment = $shipment ?? new Shipment();

        return $shipment !== null && $this->user()?->can('pack', $shipment);
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();
        $shipmentId = $this->shipment->id ?? 0;

        return [
            'packages_count' => ['nullable', 'integer', 'min:0'],
            'gross_weight' => ['nullable', 'numeric', 'min:0'],
            'net_weight' => ['nullable', 'numeric', 'min:0'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => [
                'required',
                'integer',
                Rule::exists('shipment_lines', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('shipment_id', $shipmentId)),
            ],
            'lines.*.packed_qty' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $lines = $this->shipment->lines()->get()->keyBy('id');

            foreach ($this->input('lines', []) as $payload) {
                $lineId = $payload['id'] ?? null;
                $packed = (float) ($payload['packed_qty'] ?? 0);

                if ($lineId && $lines->has($lineId)) {
                    $line = $lines->get($lineId);
                    if ($packed > (float) $line->picked_qty) {
                        $validator->errors()->add("lines.{$lineId}.packed_qty", __('Packed quantity cannot exceed picked quantity.'));
                    }
                }
            }
        });
    }
}
