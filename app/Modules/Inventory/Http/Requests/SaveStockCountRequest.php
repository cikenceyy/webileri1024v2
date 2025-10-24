<?php

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaveStockCountRequest extends FormRequest
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
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($q) => $companyId ? $q->where('company_id', $companyId) : $q),
            ],
            'bin_id' => [
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
            'lines.*.qty_expected' => ['nullable', 'numeric'],
            'lines.*.qty_counted' => ['required', 'numeric'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $companyId = app()->bound('company') ? app('company')->id : null;
            $warehouseId = (int) $this->input('warehouse_id');

            if ($this->filled('bin_id')) {
                $binId = (int) $this->input('bin_id');
                $valid = DB::table('warehouse_bins')
                    ->where('id', $binId)
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->value('warehouse_id');

                if ((int) $valid !== $warehouseId) {
                    $validator->errors()->add('bin_id', 'Seçilen raf depo ile uyuşmuyor.');
                }
            }
        });
    }
}
