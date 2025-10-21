<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/__healthz', function () {
    $overall = 'ok';
    $checks = [];

    try {
        $startedAt = microtime(true);
        DB::select('select 1');
        $checks['database'] = [
            'status' => 'ok',
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ];
    } catch (\Throwable $exception) {
        $overall = 'fail';
        $checks['database'] = [
            'status' => 'fail',
            'error' => Str::limit($exception->getMessage(), 120),
        ];
    }

    try {
        $cacheKey = 'healthz:ping';
        Cache::store()->put($cacheKey, 'pong', 10);
        $checks['cache'] = [
            'status' => Cache::store()->get($cacheKey) === 'pong' ? 'ok' : 'fail',
        ];
    } catch (\Throwable $exception) {
        $overall = 'fail';
        $checks['cache'] = [
            'status' => 'fail',
            'error' => Str::limit($exception->getMessage(), 120),
        ];
    }

    try {
        $defaultConnection = config('queue.default');
        $connectionConfig = config("queue.connections.$defaultConnection", []);

        if (($connectionConfig['driver'] ?? null) === 'database') {
            $table = $connectionConfig['table'] ?? 'jobs';
            $pending = DB::table($table)->count();
            $queueStatus = $pending > 200 ? 'degraded' : 'ok';
            $checks['queue'] = [
                'status' => $queueStatus,
                'pending' => $pending,
            ];

            if ($queueStatus !== 'ok') {
                $overall = 'degraded';
            }
        } else {
            $checks['queue'] = [
                'status' => 'ok',
                'driver' => $connectionConfig['driver'] ?? 'unknown',
            ];
        }
    } catch (\Throwable $exception) {
        $overall = 'fail';
        $checks['queue'] = [
            'status' => 'fail',
            'error' => Str::limit($exception->getMessage(), 120),
        ];
    }

    $statusCode = $overall === 'fail' ? 503 : 200;

    return response()->json([
        'status' => $overall,
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $statusCode, [
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});
