<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaveStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = app()->bound('company') ? app('company')->id : null;

        return [
            'doc_no' => ['nullable', 'string', 'max:64'],
            'from_warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'from_bin_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_bins', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'to_warehouse_id' => [
                'required',
                'integer',
                'different:from_warehouse_id',
                Rule::exists('warehouses', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'to_bin_id' => [
                'nullable',
                'integer',
                Rule::exists('warehouse_bins', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'lines.*.qty' => ['required', 'numeric', 'gt:0'],
            'lines.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $companyId = app()->bound('company') ? app('company')->id : null;
            $fromWarehouse = (int) $this->input('from_warehouse_id');
            $toWarehouse = (int) $this->input('to_warehouse_id');

            if ($this->filled('from_bin_id')) {
                $binId = (int) $this->input('from_bin_id');
                $valid = DB::table('warehouse_bins')
                    ->where('id', $binId)
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->value('warehouse_id');

                if ((int) $valid !== $fromWarehouse) {
                    $validator->errors()->add('from_bin_id', 'Seçilen raf çıkış deposuna ait değil.');
                }
            }

            if ($this->filled('to_bin_id')) {
                $binId = (int) $this->input('to_bin_id');
                $valid = DB::table('warehouse_bins')
                    ->where('id', $binId)
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->value('warehouse_id');

                if ((int) $valid !== $toWarehouse) {
                    $validator->errors()->add('to_bin_id', 'Seçilen raf varış deposuna ait değil.');
                }
            }
        });
    }
}
