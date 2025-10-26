<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TablekitScan extends Command
{
    protected $signature = 'tablekit:scan {--strict : Fail when legacy tables are found} {--path=* : Additional paths to scan}';

    protected $description = 'Scan Blade templates for legacy table usage outside of TableKit.';

    /**
     * @var array<int, string>
     */
    protected array $defaultPaths = [
        'app',
        'resources/views',
    ];

    /**
     * @var array<int, string>
     */
    protected array $ignorePatterns = [
        '/components/tablekit/',
        '/components/ui-table.blade.php',
        '/mail/',
        '/print',
        '/_form.blade.php',
        '/partials/',
    ];

    public function handle(): int
    {
        $paths = $this->option('path');
        $paths = is_array($paths) && $paths !== [] ? $paths : $this->defaultPaths;

        $files = $this->gatherBladeFiles($paths);
        $issues = [];

        foreach ($files as $file) {
            $contents = File::get($file);
            if (! $this->containsLegacyTable($contents)) {
                continue;
            }

            $issues[] = [
                'path' => $file,
                'view' => $this->inferViewName($file),
                'suggestedPreset' => $this->suggestPreset($file),
            ];
        }

        $count = count($issues);
        $this->output->writeln(sprintf('<comment>Legacy tables found:</comment> %d', $count));

        $logPath = storage_path('logs/tablekit-scan.json');
        File::ensureDirectoryExists(dirname($logPath));
        File::put($logPath, json_encode($issues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        if ($count > 0) {
            foreach ($issues as $issue) {
                $this->line(sprintf(' • %s', $issue['path']));
            }
        }

        return $count > 0 && $this->option('strict') ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    protected function gatherBladeFiles(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            if (! File::exists($fullPath)) {
                continue;
            }

            $found = File::glob($fullPath.'/**/*.blade.php');
            if (! $found) {
                continue;
            }

            foreach ($found as $file) {
                if ($this->shouldIgnore($file)) {
                    continue;
                }
                $files[] = $file;
            }
        }

        sort($files);

        return $files;
    }

    protected function shouldIgnore(string $path): bool
    {
        foreach ($this->ignorePatterns as $pattern) {
            if (Str::contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function containsLegacyTable(string $contents): bool
    {
        if (str_contains($contents, 'tablekit:ignore')) {
            return false;
        }

        if (! preg_match('/<table(?![^>]*data-tablekit-ignore)/i', $contents)) {
            return false;
        }

        if (preg_match('/<x-?table/i', $contents)) {
            return false;
        }

        return true;
    }

    protected function inferViewName(string $path): string
    {
        if (Str::startsWith($path, resource_path('views'))) {
            $relative = Str::after($path, resource_path('views/'));
            return str_replace(['/', '.blade.php'], ['.', ''], $relative);
        }

        $relative = Str::after($path, base_path('app/'));
        $relative = preg_replace('#^Modules/([^/]+)/Resources/views/#', '$1::', $relative);
        $relative = preg_replace('#^Consoles/Resources/views/#', 'consoles::', $relative);

        return str_replace(['/', '.blade.php'], ['.', ''], $relative ?? '');
    }

    protected function suggestPreset(string $path): ?string
    {
        $view = $this->inferViewName($path);

        return match (true) {
            Str::contains($view, 'invoices.index') => 'invoices',
            Str::contains($view, 'shipments') => 'shipments',
            Str::contains($view, 'receipts') => 'grns',
            Str::contains($view, 'orders.index') => 'orders',
            Str::contains($view, 'workorders.index') => 'workorders',
            Str::contains($view, 'transfers.index') => 'transfers',
            Str::contains($view, 'products.index') => 'products',
            default => null,
        };
    }
}
