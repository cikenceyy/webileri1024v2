<?php

namespace App\Modules\Logistics\Http\Requests\Admin;

use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptReceiveRequest extends FormRequest
{
    protected GoodsReceipt $receipt;

    public function authorize(): bool
    {
        /** @var GoodsReceipt|null $receipt */
        $receipt = $this->route('receipt');
        $this->receipt = $receipt?->loadMissing('lines') ?? new GoodsReceipt();

        return $receipt !== null && $this->user()?->can('receive', $receipt);
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();
        $receiptId = $this->receipt->id ?? 0;

        return [
            'warehouse_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => [
                'required',
                'integer',
                Rule::exists('goods_receipt_lines', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('receipt_id', $receiptId)),
            ],
            'lines.*.qty_expected' => ['nullable', 'numeric', 'min:0'],
            'lines.*.qty_received' => ['required', 'numeric', 'min:0'],
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
            'lines.*.variance_reason' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->receipt->exists) {
                return;
            }

            $headerWarehouse = $this->input('warehouse_id') ?: $this->receipt->warehouse_id;
            $lines = collect($this->input('lines', []))->keyBy('id');
            $hasReceived = false;

            foreach ($this->receipt->lines as $line) {
                $payload = $lines->get($line->id);
                if (! $payload) {
                    continue;
                }

                $qty = (float) ($payload['qty_received'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $hasReceived = true;
                $lineWarehouse = $payload['warehouse_id'] ?? $line->warehouse_id;
                $effectiveWarehouse = $lineWarehouse ?: $headerWarehouse;

                if (! $effectiveWarehouse) {
                    $validator->errors()->add(
                        "lines.{$line->id}.warehouse_id",
                        __('Mal kabul satırı için depo seçilmelidir.')
                    );
                }
            }

            if (! $hasReceived) {
                $validator->errors()->add('lines', __('En az bir satır için alınan miktar girilmelidir.'));
            }
        });
    }
}
