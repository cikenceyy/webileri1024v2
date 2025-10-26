<?php

namespace App\Modules\Inventory\Http\Requests;

use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Support\DriveStructure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Modules\Inventory\Domain\Models\Product::class) ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->attributes->get('company_id') ?? (app()->bound('company') ? app('company')->id : null);

        $productFolder = DriveStructure::normalizeFolderKey('products', Media::MODULE_INVENTORY);

        return [
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->where(fn ($query) => $companyId ? $query->where('company_id', $companyId) : $query),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:16'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'barcode' => ['nullable', 'string', 'max:64'],
            'base_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'reorder_point' => ['nullable', 'numeric', 'min:0'],
            'media_id' => [
                'nullable',
                Rule::exists('media', 'id')->where(function ($query) use ($companyId, $productFolder) {
                    return $query
                        ->where('company_id', $companyId)
                        ->where('category', $productFolder)
                        ->whereNull('deleted_at');
                }),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
