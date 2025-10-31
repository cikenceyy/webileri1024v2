<?php

namespace App\Core\Support\TableKit;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Filters
{
    /**
     * Normalizes a scalar filter value from the TableKit request payload.
     */
    public static function scalar(Request $request, string $key): ?string
    {
        $filters = $request->query('filters', []);
        $value = Arr::get($filters, $key);

        if (is_array($value)) {
            $value = Arr::get($value, 'value', Arr::first($value));
        }

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if (is_string($value) || is_numeric($value)) {
            $value = trim((string) $value);
        } else {
            $value = null;
        }

        if ($value === null || $value === '') {
            $direct = $request->query($key);

            if ($direct === null || $direct === '') {
                return null;
            }

            return trim((string) $direct) ?: null;
        }

        return $value;
    }

    /**
     * Extracts a multi-select filter array (enum/badge) from the request.
     *
     * @return array<int, string>
     */
    public static function multi(Request $request, string $key): array
    {
        $filters = $request->query('filters', []);
        $value = Arr::get($filters, $key);

        $items = [];

        if ($value !== null) {
            foreach (Arr::wrap($value) as $entry) {
                if ($entry === null) {
                    continue;
                }

                if ($entry instanceof \BackedEnum) {
                    $entry = $entry->value;
                }

                if (! is_scalar($entry)) {
                    continue;
                }

                $normalized = trim((string) $entry);

                if ($normalized === '') {
                    continue;
                }

                $items[] = $normalized;
            }
        }

        if (! empty($items)) {
            return array_values(array_unique($items));
        }

        $direct = $request->query($key);

        if ($direct === null || $direct === '') {
            return [];
        }

        $directItems = [];

        foreach (Arr::wrap($direct) as $entry) {
            if ($entry === null) {
                continue;
            }

            if ($entry instanceof \BackedEnum) {
                $entry = $entry->value;
            }

            if (! is_scalar($entry)) {
                continue;
            }

            $normalized = trim((string) $entry);

            if ($normalized === '') {
                continue;
            }

            $directItems[] = $normalized;
        }

        return array_values(array_unique($directItems));
    }

    /**
     * Extracts a numeric filter value and casts it to float when possible.
     */
    public static function number(Request $request, string $key): ?float
    {
        $value = self::scalar($request, $key);

        if ($value === null) {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Extracts a date range filter from the request.
     *
     * @return array{0: string|null, 1: string|null}
     */
    public static function range(Request $request, string $key): array
    {
        $filters = $request->query('filters', []);
        $range = Arr::get($filters, $key, []);

        $from = null;
        $to = null;

        if (is_array($range)) {
            $from = self::normalizeDate(Arr::get($range, 'from'));
            $to = self::normalizeDate(Arr::get($range, 'to'));
        }

        if ($from === null) {
            $from = self::normalizeDate($request->query($key . '_from'));
        }

        if ($to === null) {
            $to = self::normalizeDate($request->query($key . '_to'));
        }

        return [$from, $to];
    }

    protected static function normalizeDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) || is_numeric($value)) {
            $candidate = trim((string) $value);

            return $candidate === '' ? null : $candidate;
        }

        return null;
    }
}
