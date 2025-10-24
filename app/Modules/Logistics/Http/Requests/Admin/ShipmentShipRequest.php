<?php

namespace App\Modules\Logistics\Http\Requests\Admin;

use App\Modules\Logistics\Domain\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;

class ShipmentShipRequest extends FormRequest
{
    protected Shipment $shipment;

    public function authorize(): bool
    {
        /** @var Shipment|null $shipment */
        $shipment = $this->route('shipment');
        $this->shipment = $shipment?->loadMissing('lines') ?? new Shipment();

        return $shipment !== null && $this->user()?->can('ship', $shipment);
    }

    public function rules(): array
    {
        return [
            'confirm' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->shipment->exists) {
                return;
            }

            $hasPackedQuantity = false;
            $defaultWarehouse = $this->shipment->warehouse_id;

            foreach ($this->shipment->lines as $line) {
                if ((float) $line->packed_qty <= 0) {
                    continue;
                }

                $hasPackedQuantity = true;
                $effectiveWarehouse = $line->warehouse_id ?: $defaultWarehouse;

                if (! $effectiveWarehouse) {
                    $validator->errors()->add(
                        "lines.{$line->id}.warehouse_id",
                        __('Sevk edilecek satır için depo seçilmelidir.')
                    );
                }
            }

            if (! $hasPackedQuantity) {
                $validator->errors()->add('lines', __('Sevk edilecek miktar bulunmuyor.'));
            }
        });
    }
}
