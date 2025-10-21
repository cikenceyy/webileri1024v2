<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'tenant', 'auth:sanctum'])
    ->prefix('marketing')
    ->as('api.marketing.')
    ->group(function (): void {
        // TODO: Add Marketing API endpoints (leads, opportunities, activities)
    });
