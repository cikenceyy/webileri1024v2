<?php

namespace App\Core\Domain\Sequencing;

use Illuminate\Database\Eloquent\Model;

class SequenceNumber extends Model
{
    protected $table = 'sequence_numbers';

    protected $fillable = [
        'company_id',
        'key',
        'prefix',
        'padding',
        'year',
        'last_number',
    ];

    protected $casts = [
        'company_id' => 'int',
        'padding' => 'int',
        'year' => 'int',
        'last_number' => 'int',
    ];
}
