
<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

it('compiles all CMS blade templates without syntax errors', function (): void {
    $basePath = app_path('Cms/Resources/views');

    foreach (File::allFiles($basePath) as $file) {
        if (!Str::endsWith($file->getFilename(), '.blade.php')) {
            continue;
        }

        $relative = Str::after($file->getPathname(), $basePath . DIRECTORY_SEPARATOR);

        expect(fn () => Blade::compileString($file->getContents()))
            ->not->toThrow(Throwable::class, "Blade compilation failed for cms::{$relative}");
    }
});
