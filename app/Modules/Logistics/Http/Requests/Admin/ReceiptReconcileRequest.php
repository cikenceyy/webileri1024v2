<?php

namespace App\Modules\Logistics\Http\Requests\Admin;

use App\Modules\Logistics\Domain\Models\GoodsReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiptReconcileRequest extends FormRequest
{
    protected GoodsReceipt $receipt;

    public function authorize(): bool
    {
        /** @var GoodsReceipt|null $receipt */
        $receipt = $this->route('receipt');
        $this->receipt = $receipt ?? new GoodsReceipt();

        return $receipt !== null && $this->user()?->can('reconcile', $receipt);
    }

    public function rules(): array
    {
        $companyId = currentCompanyId();
        $receiptId = $this->receipt->id ?? 0;

        return [
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => [
                'required',
                'integer',
                Rule::exists('goods_receipt_lines', 'id')->where(fn ($query) => $query
                    ->where('company_id', $companyId)
                    ->where('receipt_id', $receiptId)),
            ],
            'lines.*.variance_reason' => ['nullable', 'string', 'max:40'],
            'lines.*.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! config('features.logistics.variance_reason_codes', true)) {
                return;
            }

            $lines = $this->receipt->lines()->get()->keyBy('id');

            foreach ($this->input('lines', []) as $payload) {
                $lineId = $payload['id'] ?? null;
                if (! $lineId || ! $lines->has($lineId)) {
                    continue;
                }

                $line = $lines->get($lineId);
                $variance = (float) $line->variance;
                if ($variance != 0.0 && empty($payload['variance_reason'])) {
                    $validator->errors()->add("lines.{$lineId}.variance_reason", __('Variance reason is required for non-zero variance.'));
                }
            }
        });
    }
}
