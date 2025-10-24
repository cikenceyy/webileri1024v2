<?php

namespace App\Modules\Marketing\Http\Requests\Admin;

use App\Modules\Marketing\Domain\Models\ReturnRequest;
use Illuminate\Foundation\Http\FormRequest;

class CloseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ReturnRequest $return */
        $return = $this->route('return');

        return $return && $return->isApproved() && ($this->user()?->can('close', $return) ?? false);
    }

    public function rules(): array
    {
        return [];
    }
}
