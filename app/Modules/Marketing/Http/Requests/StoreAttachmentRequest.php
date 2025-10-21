<?php

namespace App\Modules\Marketing\Http\Requests;

use App\Modules\Marketing\Domain\Models\Attachment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Attachment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'related_type' => ['required', 'string', 'max:255'],
            'related_id' => ['required', 'integer'],
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ];
    }
}
