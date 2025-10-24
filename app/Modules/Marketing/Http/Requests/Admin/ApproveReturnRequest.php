<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Marketing\Domain\Models\ReturnRequest;
use Illuminate\Foundation\Http\FormRequest;

class ApproveReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ReturnRequest $return */
        $return = $this->route('return');

        return $return && $return->isOpen() && ($this->user()?->can('approve', $return) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
