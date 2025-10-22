<?php

namespace App\Core\Orchestrations\Concerns;

use RuntimeException;

trait ResolvesTenant
{
    protected function resolveCompanyId(): int
    {
        $companyId = currentCompanyId() ?? tenant()?->id ?? null;

        if (! $companyId) {
            throw new RuntimeException('Unable to resolve tenant company identifier.');
        }

        return (int) $companyId;
    }
}
