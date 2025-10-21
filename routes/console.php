<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('queue:prune-batches --hours=48')
    ->dailyAt('01:15')
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command('model:prune')
    ->dailyAt('03:00')
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command('queue:retry all')
    ->everyTenMinutes()
    ->runInBackground();

Schedule::command('webileri:sequence:audit')
    ->weeklyOn(1, '02:30')
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command('webileri:tenancy:audit')
    ->weeklyOn(1, '03:30')
    ->runInBackground()
    ->withoutOverlapping();
