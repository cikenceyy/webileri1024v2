<?php

namespace App\Core\Support\Nav;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class NavGate
{
    public static function visible(null|string|array $abilities = null, null|string|array $features = null): bool
    {
        foreach (Arr::wrap($features) as $feature) {
            if ($feature === null) {
                continue;
            }

            if (! config('features.' . $feature, true)) {
                return false;
            }
        }

        if ($abilities === null) {
            return true;
        }

        foreach (Arr::wrap($abilities) as $ability) {
            if ($ability === null) {
                continue;
            }

            if (str_starts_with($ability, 'gate:')) {
                if (! Gate::allows(substr($ability, 5))) {
                    return false;
                }

                continue;
            }

            if (! Gate::allows($ability)) {
                return false;
            }
        }

        return true;
    }
}
