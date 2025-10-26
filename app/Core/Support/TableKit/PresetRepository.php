<?php

namespace App\Core\Support\TableKit;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PresetRepository
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $cache = [];

    public function load(string $name): ?array
    {
        $key = Str::snake($name);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $path = base_path('app/Core/Support/TableKit/Presets/'.Str::snake($name).'.php');

        if (! File::exists($path)) {
            return $this->cache[$key] = null;
        }

        try {
            /** @var array<string, mixed> $preset */
            $preset = File::getRequire($path);
        } catch (FileNotFoundException) {
            return $this->cache[$key] = null;
        }

        $columns = Arr::get($preset, 'columns', []);
        $options = Arr::get($preset, 'options', []);

        return $this->cache[$key] = [
            'columns' => is_array($columns) ? $columns : [],
            'options' => is_array($options) ? $options : [],
        ];
    }
}
