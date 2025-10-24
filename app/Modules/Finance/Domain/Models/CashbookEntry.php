<?php

namespace App\Modules\Finance\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class CashbookEntry extends Model
{
    use BelongsToCompany;

    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    protected $fillable = [
        'company_id',
        'direction',
        'amount',
        'occurred_at',
        'account',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'date',
    ];

    public static function directions(): array
    {
        return [self::DIRECTION_IN, self::DIRECTION_OUT];
    }
}
