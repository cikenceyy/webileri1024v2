<?php

namespace App\Modules\Drive\Support;

use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DriveStructure
{
    public static function defaultModule(): string
    {
        $module = (string) config('drive.defaults.module', Media::MODULE_DEFAULT);

        return self::normalizeModule($module);
    }

    public static function defaultFolder(?string $module = null): string
    {
        $module = $module ? self::normalizeModule($module) : null;
        $default = strtolower((string) config('drive.defaults.folder', 'documents'));
        $folders = self::folderKeys($module);

        if ($default && in_array($default, $folders, true)) {
            return $default;
        }

        return $folders[0] ?? 'documents';
    }

    public static function normalizeModule(?string $module): string
    {
        $module = strtolower((string) $module ?: '');

        return in_array($module, self::moduleKeys(), true)
            ? $module
            : self::moduleKeys()[0];
    }

    public static function normalizeFolderKey(?string $folder, ?string $module = null): string
    {
        $module = $module ? self::normalizeModule($module) : null;
        $folder = strtolower((string) $folder ?: '');
        $folders = self::folderKeys($module);

        if ($folder && in_array($folder, $folders, true)) {
            return $folder;
        }

        return $folders[0] ?? self::defaultFolder($module);
    }

    public static function folderExists(string $folder, ?string $module = null): bool
    {
        $folders = self::folderKeys($module);

        return in_array($folder, $folders, true);
    }

    public static function folderKeys(?string $module = null): array
    {
        if ($module) {
            return array_values(array_unique(array_filter(array_map(
                static fn ($definition) => $definition['key'],
                self::moduleFolderDefinitions($module)
            ))));
        }

        return array_keys(self::folders());
    }

    public static function folders(): array
    {
        $folders = config('drive.folders', []);

        return array_map(static function ($definition, $key) {
            $definition['key'] = $key;

            return self::normalizeFolderDefinition($definition);
        }, $folders, array_keys($folders));
    }

    public static function folder(string $key, array $overrides = []): array
    {
        $definition = self::folders()[$key] ?? ['key' => $key];

        if ($overrides) {
            $definition = array_replace_recursive($definition, $overrides);
        }

        return self::normalizeFolderDefinition($definition);
    }

    public static function folderLabel(string $key, ?string $module = null): string
    {
        $moduleDefinitions = $module ? self::moduleFolderDefinitions($module) : [];
        $override = collect($moduleDefinitions)->firstWhere('key', $key) ?: [];
        $definition = self::folder($key, $override);

        $label = Arr::get($definition, 'label');

        return $label ?: Str::headline($key);
    }

    public static function folderType(string $key): string
    {
        return Arr::get(self::folder($key), 'type', 'document');
    }

    public static function folderExtensions(string $key): array
    {
        return self::normalizeList(Arr::get(self::folder($key), 'ext', []));
    }

    public static function folderMimes(string $key): array
    {
        return self::normalizeList(Arr::get(self::folder($key), 'mimes', []));
    }

    public static function folderMaxBytes(string $key): int
    {
        $global = (int) config('drive.max_upload_bytes', 50 * 1024 * 1024);
        $folderLimit = (int) Arr::get(self::folder($key), 'max', $global);

        return (int) min($global, $folderLimit);
    }

    public static function moduleKeys(): array
    {
        $modules = config('drive.modules', []);
        $keys = array_keys($modules);

        if ($keys) {
            return array_values(array_unique(array_map('strtolower', $keys)));
        }

        return Media::moduleKeys();
    }

    public static function moduleOptions(): array
    {
        $modules = config('drive.modules', []);

        if (! $modules) {
            return Media::MODULE_LABELS;
        }

        return collect($modules)
            ->map(fn ($definition, $key) => Arr::get($definition, 'label', Media::MODULE_LABELS[$key] ?? Str::of($key)->headline()))
            ->all();
    }

    public static function moduleFolderDefinitions(?string $module = null): array
    {
        $module = $module ? self::normalizeModule($module) : self::defaultModule();
        $modules = config('drive.modules', []);
        $moduleDefinition = $modules[$module] ?? [];
        $folders = Arr::get($moduleDefinition, 'folders', []);

        return collect($folders)
            ->map(function ($item) {
                if (is_string($item)) {
                    return self::folder($item);
                }

                if (is_array($item)) {
                    $key = $item['key'] ?? null;
                    if (! $key) {
                        return null;
                    }

                    return self::folder($key, Arr::except($item, ['key']));
                }

                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function moduleAllowsFolder(string $module, string $folder): bool
    {
        return in_array($folder, self::folderKeys($module), true);
    }

    public static function foldersByType(string $type): array
    {
        return collect(self::folders())
            ->filter(fn ($definition) => ($definition['type'] ?? 'document') === $type)
            ->map(fn ($definition) => $definition['key'])
            ->values()
            ->all();
    }

    public static function navigation(): array
    {
        return collect(self::moduleOptions())
            ->map(function ($label, $module) {
                return [
                    'module' => $module,
                    'label' => $label,
                    'folders' => self::moduleFolderDefinitions($module),
                ];
            })
            ->values()
            ->all();
    }

    protected static function normalizeFolderDefinition(array $definition): array
    {
        $definition['key'] = strtolower((string) ($definition['key'] ?? ''));
        $definition['label'] = $definition['label'] ?? Str::headline($definition['key']);
        $definition['type'] = $definition['type'] ?? 'document';
        $definition['ext'] = self::normalizeList($definition['ext'] ?? []);
        $definition['mimes'] = self::normalizeList($definition['mimes'] ?? []);
        $definition['max'] = (int) ($definition['max'] ?? config('drive.max_upload_bytes', 50 * 1024 * 1024));

        return $definition;
    }

    protected static function normalizeList(array $items): array
    {
        return array_values(array_unique(array_filter(array_map(static function ($value) {
            return strtolower((string) $value);
        }, $items))));
    }
}
