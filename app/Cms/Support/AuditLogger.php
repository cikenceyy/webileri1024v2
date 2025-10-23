<?php

namespace App\Cms\Support;

use App\Cms\Models\CmsAudit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class AuditLogger
{
    public function __construct(protected CmsRepository $repository)
    {
    }

    public function log(string $page, string $locale, array $before, array $after): void
    {
        $user = $this->currentUser();
        $companyId = $this->repository->companyId();

        $beforeFlat = Arr::dot($before);
        $afterFlat = Arr::dot($after);

        foreach ($afterFlat as $key => $value) {
            $previous = $beforeFlat[$key] ?? null;
            if ($this->normaliseValue($previous) === $this->normaliseValue($value)) {
                continue;
            }

            CmsAudit::create([
                'company_id' => $companyId,
                'user_id' => $user?->getAuthIdentifier(),
                'page' => $page,
                'locale' => $locale,
                'field' => $key,
                'before' => $this->stringify($previous),
                'after' => $this->stringify($value),
            ]);
        }
    }

    protected function currentUser(): ?Authenticatable
    {
        return auth()->user();
    }

    protected function normaliseValue(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return trim((string) $value) ?: null;
        }

        try {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function stringify(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (is_scalar($value)) {
            $string = trim((string) $value);

            return $string === '' ? null : $string;
        }

        try {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable) {
            return null;
        }
    }
}
