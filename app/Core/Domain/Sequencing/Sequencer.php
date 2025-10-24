<?php

namespace App\Core\Domain\Sequencing;

use App\Core\Domain\Sequencing\Exceptions\SequenceCollisionException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Sequencer
{
    public function next(int $companyId, string $key, string $prefix, int $padding, string $resetPolicy = 'never'): string
    {
        $normalizedKey = Str::of($key)->slug('_');
        $prefix = $prefix !== '' ? $prefix : Str::upper($normalizedKey);
        $padding = max(3, min(8, $padding));
        $policy = in_array($resetPolicy, ['yearly', 'never'], true) ? $resetPolicy : 'never';
        $year = $policy === 'yearly' ? Carbon::now()->year : null;

        return DB::transaction(function () use ($companyId, $normalizedKey, $prefix, $padding, $year) {
            $attempts = 0;

            do {
                $attempts++;

                $query = SequenceNumber::query()
                    ->where('company_id', $companyId)
                    ->where('key', $normalizedKey)
                    ->when($year !== null, fn ($builder) => $builder->where('year', $year))
                    ->when($year === null, fn ($builder) => $builder->whereNull('year'))
                    ->lockForUpdate();

                $sequence = $query->first();

                if (! $sequence) {
                    $sequence = new SequenceNumber([
                        'company_id' => $companyId,
                        'key' => $normalizedKey,
                        'year' => $year,
                        'last_number' => 0,
                    ]);
                }

                $sequence->prefix = $prefix;
                $sequence->padding = $padding;
                $sequence->year = $year;
                $sequence->last_number = ($sequence->last_number ?? 0) + 1;

                try {
                    $sequence->save();
                } catch (QueryException $exception) {
                    if ($this->isUniqueConstraintViolation($exception) && $attempts < 3) {
                        continue;
                    }

                    throw new SequenceCollisionException('Sequence generation failed due to concurrent access.', 0, $exception);
                }

                $number = str_pad((string) $sequence->last_number, $padding, '0', STR_PAD_LEFT);

                return $prefix . $number;
            } while ($attempts < 3);

            throw new SequenceCollisionException('Unable to reserve a new sequence value.');
        });
    }

    protected function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $code = (string) $exception->getCode();

        return in_array($code, ['23000', '23505'], true);
    }
}
