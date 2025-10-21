<?php

namespace App\Core\Bus\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class NextNumber
{
    /**
     * Generate the next document number for a tenant-scoped sequence.
     *
     * @param  array{prefix?:string,padding?:int,reset_period?:string,scope?:string,date?:\DateTimeInterface,idempotency_key?:string,context?:mixed}  $options
     */
    public function __invoke(int $companyId, string $key, array $options = []): string
    {
        $key = Str::upper($key);
        $date = Carbon::parse($options['date'] ?? now());
        $scope = Arr::get($options, 'scope');
        $idempotencyKey = Arr::get($options, 'idempotency_key');

        if ($idempotencyKey) {
            $issued = DB::table('issued_numbers')
                ->where('company_id', $companyId)
                ->where('key', $key)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($issued) {
                return (string) $issued->number;
            }
        }

        $contextHash = null;
        if (array_key_exists('context', $options)) {
            $contextHash = hash('sha256', json_encode($options['context']));
        }

        $formatted = DB::transaction(function () use ($companyId, $key, $options, $date, $scope, $contextHash) {
            $sequenceQuery = DB::table('sequences')
                ->where('company_id', $companyId)
                ->where('key', $key)
                ->when($scope, fn ($q) => $q->where('scope', $scope))
                ->lockForUpdate();

            $sequence = $sequenceQuery->first();

            if (! $sequence) {
                DB::table('sequences')->insert([
                    'company_id' => $companyId,
                    'key' => $key,
                    'current' => 0,
                    'prefix' => Arr::get($options, 'prefix', $key),
                    'padding' => Arr::get($options, 'padding', 4),
                    'reset_period' => Arr::get($options, 'reset_period', 'yearly'),
                    'scope' => $scope,
                    'last_reset_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $sequence = $sequenceQuery->first();
            }

            $prefix = Arr::get($options, 'prefix', $sequence->prefix ?? $key);
            $padding = (int) Arr::get($options, 'padding', $sequence->padding ?? 4);
            $resetPeriod = Arr::get($options, 'reset_period', $sequence->reset_period ?? 'yearly');

            if ($prefix !== $sequence->prefix || $padding !== (int) $sequence->padding || $resetPeriod !== $sequence->reset_period) {
                DB::table('sequences')->where('id', $sequence->id)->update([
                    'prefix' => $prefix,
                    'padding' => $padding,
                    'reset_period' => $resetPeriod,
                ]);

                $sequence = (object) array_merge((array) $sequence, [
                    'prefix' => $prefix,
                    'padding' => $padding,
                    'reset_period' => $resetPeriod,
                ]);
            }

            $lastReset = $sequence->last_reset_at ? Carbon::parse($sequence->last_reset_at) : null;
            $shouldReset = false;

            if ($resetPeriod === 'yearly') {
                $shouldReset = ! $lastReset || $lastReset->year !== $date->year;
            } elseif ($resetPeriod === 'monthly') {
                $shouldReset = ! $lastReset || $lastReset->format('Ym') !== $date->format('Ym');
            }

            if ($shouldReset) {
                DB::table('sequences')->where('id', $sequence->id)->update([
                    'current' => 0,
                    'last_reset_at' => $date->copy()->startOfDay(),
                ]);

                $sequence = (object) array_merge((array) $sequence, [
                    'current' => 0,
                    'last_reset_at' => $date->copy()->startOfDay()->toDateTimeString(),
                ]);
            }

            $next = ((int) $sequence->current) + 1;

            DB::table('sequences')->where('id', $sequence->id)->update([
                'current' => $next,
                'last_reset_at' => $date->copy()->startOfDay(),
                'updated_at' => now(),
            ]);

            $serial = str_pad((string) $next, $padding, '0', STR_PAD_LEFT);
            $yyyy = $date->format('Y');
            $mm = $date->format('m');

            return match ($resetPeriod) {
                'monthly' => sprintf('%s-%s%s-%s', $prefix, $yyyy, $mm, $serial),
                'none' => sprintf('%s-%s', $prefix, $serial),
                default => sprintf('%s-%s-%s', $prefix, $yyyy, $serial),
            };
        });

        if ($idempotencyKey) {
            try {
                DB::table('issued_numbers')->updateOrInsert([
                    'company_id' => $companyId,
                    'key' => $key,
                    'idempotency_key' => $idempotencyKey,
                ], [
                    'number' => $formatted,
                    'context_hash' => $contextHash,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (Throwable $exception) {
                $existing = DB::table('issued_numbers')
                    ->where('company_id', $companyId)
                    ->where('key', $key)
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing) {
                    return (string) $existing->number;
                }

                throw $exception;
            }
        }

        return $formatted;
    }
}
