<?php

namespace App\Core\Tenancy\Traits;

use App\Core\Support\Models\Company;
use App\Core\Tenancy\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope());

        static::creating(function (Model $model): void {
            if ($model->getAttribute('company_id')) {
                return;
            }

            $companyId = currentCompanyId();

            if ($companyId) {
                $model->setAttribute('company_id', $companyId);
            }
        });
    }

    public function initializeBelongsToCompany(): void
    {
        $this->mergeFillable(['company_id']);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
