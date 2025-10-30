<?php

namespace App\Core\ConsoleKit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Komut çubuğunda gösterilecek eylemleri tenant ve yetki filtrelerine göre
 * süzen yardımcı sınıf.
 */
class CommandPalette
{
    /**
     * @param  array<int, array<string, mixed>>  $commands
     */
    public function __construct(private array $commands)
    {
    }

    /**
     * Statik kurucu kolaylığı.
     *
     * @param  array<int, array<string, mixed>>  $commands
     */
    public static function make(array $commands): self
    {
        return new self($commands);
    }

    /**
     * Kullanıcının yetkilerine göre komut listesini döndürür.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resolveFor(?Authenticatable $user): array
    {
        return collect($this->commands)
            ->map(function (array $command) {
                $command['id'] ??= Str::uuid()->toString();
                $command['shortcut'] ??= null;
                $command['description'] ??= null;

                return $command;
            })
            ->filter(function (array $command) use ($user) {
                $permission = Arr::get($command, 'permission');
                if (! $permission) {
                    return true;
                }

                if (! $user) {
                    return false;
                }

                return $user->can((string) $permission);
            })
            ->values()
            ->all();
    }
}
