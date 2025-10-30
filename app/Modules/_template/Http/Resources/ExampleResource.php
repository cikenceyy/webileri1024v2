<?php

namespace App\Modules\_template\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API çıktılarınızı standartlaştırmak için JsonResource şablonu.
 * Array dönüşünü gerçek veri yapınıza göre özelleştirin.
 */
class ExampleResource extends JsonResource
{
    /**
     * Kaynağı diziye dönüştürür.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id ?? null,
            'name' => $this->resource->name ?? null,
        ];
    }
}
