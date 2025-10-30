<?php

namespace App\Core\Tenancy\Support;

/**
 * Domain/host değerlerini normalize ederek güvenli karşılaştırmalar yapılmasını sağlar.
 */
class DomainNormalizer
{
    /**
     * Host değerini küçültüp boşluk/port/son nokta gibi varyasyonları temizler.
     */
    public static function normalize(string $host): string
    {
        $normalized = strtolower(trim($host));
        $normalized = preg_replace('/:\d+$/', '', $normalized ?? '');
        $normalized = rtrim($normalized, '.');

        return $normalized !== null ? trim($normalized) : '';
    }

    /**
     * www varyasyonlarını da içeren olası host listesini döner.
     *
     * @return array<int, string>
     */
    public static function candidates(string $host, bool $stripWww = true): array
    {
        $primary = self::normalize($host);

        if ($primary === '') {
            return [];
        }

        $candidates = [$primary];

        if ($stripWww) {
            if (str_starts_with($primary, 'www.')) {
                $candidates[] = substr($primary, 4);
            } else {
                $candidates[] = 'www.' . $primary;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }
}
