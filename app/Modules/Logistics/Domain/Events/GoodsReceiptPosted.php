<?php

namespace App\Modules\Logistics\Domain\Events;

use App\Modules\Logistics\Domain\Models\GoodsReceipt;

class GoodsReceiptPosted
{
    public function __construct(public GoodsReceipt $receipt)
    {
    }
}
