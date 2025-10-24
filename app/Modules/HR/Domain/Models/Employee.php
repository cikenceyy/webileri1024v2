<?php

namespace App\Modules\HR\Domain\Models;

use App\Core\Tenancy\Traits\BelongsToCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'email',
        'phone',
        'department_id',
        'title_id',
        'employment_type_id',
        'hire_date',
        'termination_date',
        'is_active',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'hire_date' => 'date',
        'termination_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
