<?php

namespace App\Core\Cache;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Tenant bazlı önbellek anahtarlarını standartlaştırmak için yardımcı sınıf.
 * Tüm anahtarlar "tenant:{id}:..." formatını takip eder ve parçalar güvenli
 * karakter setine normalize edilir.
 */
class Keys
{
    /**
     * Belirtilen şirket için parçaları birleştirerek standart bir anahtar üretir.
     *
     * @param  array<int, string>|string  $parts  Anahtar gövdesini oluşturan parçalar
     * @param  string|null  $suffix  Versiyon vb. opsiyonel sonek
     */
    public static function forTenant(int $companyId, array|string $parts, ?string $suffix = null): string
    {
        $segments = array_filter(array_map(
            fn (string $segment): string => self::normalizeSegment($segment),
            Arr::map(Arr::wrap($parts), fn ($value) => (string) $value)
        ));

        $key = 'tenant:' . $companyId;

        if ($segments !== []) {
            $key .= ':' . implode(':', $segments);
        }

        if ($suffix !== null && $suffix !== '') {
            $normalized = self::normalizeSegment($suffix);
            if ($normalized !== '') {
                $key .= ':' . $normalized;
            }
        }

        return $key;
    }

    private static function normalizeSegment(string $segment): string
    {
        $value = Str::of($segment)
            ->lower()
            ->replace(['\\', '/', '.', ' '], ['_', '_', '_', '_'])
            ->replaceMatches('/[^a-z0-9:_-]+/u', '')
            ->trim('_:-')
            ->toString();

        return $value !== '' ? $value : 'item';
    }
}
