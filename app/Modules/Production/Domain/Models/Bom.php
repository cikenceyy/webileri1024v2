<?php

namespace App\Modules\Production\Domain\Models;

use App\Modules\Inventory\Domain\Models\Product;

class Bom
{
    public function __construct(
        public ?Product $product = null,
    ) {
    }

    public static function forProduct(Product $product): self
    {
        return new self($product);
    }
}
