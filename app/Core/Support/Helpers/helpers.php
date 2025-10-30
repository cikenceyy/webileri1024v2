<?php

use App\Core\Bus\Actions\NextNumber;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

if (! function_exists('currentCompanyId')) {
    function currentCompanyId(): ?int
    {
        if (App::bound('request')) {
            $request = request();

            if ($request && $request->attributes->has('company_id')) {
                $value = $request->attributes->get('company_id');

                return is_numeric($value) ? (int) $value : null;
            }
        }

        if (App::bound('company')) {
            $company = App::make('company');

            if (is_object($company) && isset($company->id)) {
                return (int) $company->id;
            }
        }

        return null;
    }
}

if (! function_exists('tenant')) {
    function tenant(): ?object
    {
        if (App::bound('company')) {
            return App::make('company');
        }

        if (Request::hasMacro('tenant') && Request::tenant()) {
            return Request::tenant();
        }

        return null;
    }
}

if (! function_exists('next_number')) {
    /**
     * @param  array{prefix?:string,padding?:int,reset_period?:string,scope?:string,date?:\DateTimeInterface,idempotency_key?:string,context?:mixed}  $options
     */
    function next_number(string $key, array $options = [], ?int $companyId = null): string
    {
        $companyId ??= currentCompanyId();

        if (! $companyId) {
            throw new \RuntimeException('Unable to resolve company id for sequence generation.');
        }

        /** @var NextNumber $action */
        $action = App::make(NextNumber::class);

        return $action($companyId, $key, $options);
    }
}

if (! function_exists('settings')) {
    /**
     * Tenant ayar deposunu döndürür.
     */
    function settings(): \App\Core\Settings\SettingsRepository
    {
        return App::make(\App\Core\Settings\SettingsRepository::class);
    }
}
