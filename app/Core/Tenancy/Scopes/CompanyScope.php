<?php

namespace App\Core\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = $this->resolveActiveCompanyId();

        if (! $companyId) {
            return;
        }

        $builder->where($model->getTable() . '.company_id', $companyId);
    }

    protected function resolveActiveCompanyId(): ?int
    {
        if (app()->bound('request')) {
            $request = request();

            if ($request && $request->attributes->has('company_id')) {
                return (int) $request->attributes->get('company_id');
            }
        }

        if (app()->bound('company')) {
            $company = app('company');

            return $company->id ?? null;
        }

        return null;
    }
}
