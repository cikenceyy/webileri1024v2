<?php

namespace App\Core\Auth;

use App\Core\Auth\Models\AuthAudit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

/**
 * Policy/permission reddi gibi olayları kayıt altına alır.
 */
class AuthorizationAuditLogger
{
    public function __construct(private readonly Request $request)
    {
    }

    /**
     * Yetki kontrolü sonrası sonucu saklar.
     *
     * @param  array<int, mixed>  $arguments
     */
    public function log(?Authenticatable $user, string $ability, bool $allowed, array $arguments = []): void
    {
        if ($allowed) {
            return;
        }

        $resource = $this->resolveResource($arguments);

        AuthAudit::query()->create([
            'company_id' => method_exists($user, 'getAttribute') ? $user?->getAttribute('company_id') : null,
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $ability,
            'resource' => $resource,
            'result' => 'denied',
            'ip_address' => $this->request->ip(),
            'user_agent' => (string) $this->request->header('User-Agent'),
            'context' => [
                'arguments' => $this->normalizeArguments($arguments),
                'url' => $this->request->fullUrl(),
            ],
        ]);
    }

    /**
     * @param  array<int, mixed>  $arguments
     */
    private function resolveResource(array $arguments): ?string
    {
        foreach ($arguments as $argument) {
            if (is_object($argument)) {
                return $argument::class;
            }
            if (is_string($argument)) {
                return $argument;
            }
        }

        return null;
    }

    /**
     * @param  array<int, mixed>  $arguments
     * @return array<int, mixed>
     */
    private function normalizeArguments(array $arguments): array
    {
        return array_map(function ($argument) {
            if (is_object($argument)) {
                if (method_exists($argument, 'getKey')) {
                    return [
                        'type' => $argument::class,
                        'id' => $argument->getKey(),
                    ];
                }

                return ['type' => $argument::class];
            }

            return $argument;
        }, $arguments);
    }
}
