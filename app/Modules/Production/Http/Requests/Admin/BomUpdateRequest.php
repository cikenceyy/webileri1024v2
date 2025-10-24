<?php

namespace App\Modules\Production\Http\Requests\Admin;

use App\Modules\Production\Domain\Models\Bom;

class BomUpdateRequest extends BomStoreRequest
{
    public function authorize(): bool
    {
        /** @var Bom|null $bom */
        $bom = $this->route('bom');

        return $bom ? ($this->user()?->can('update', $bom) ?? false) : false;
    }
}
