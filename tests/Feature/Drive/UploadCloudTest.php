<?php

namespace Tests\Feature\Drive;

use App\Modules\Drive\Domain\DriveStorage;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCloudTest extends TestCase
{
    public function test_it_generates_temporary_urls_for_cloud_storage(): void
    {
        Storage::fake('s3');
        Config::set('drive.disk', 's3');
        Config::set('filesystems.default', 's3');
        Config::set('filesystems.disks.s3.url', 'https://cdn.example.test');

        $storage = app(DriveStorage::class);
        $file = UploadedFile::fake()->create('rapor.csv', 4, 'text/csv');

        $stored = $storage->put($file, 10, 'cms', 'documents');

        $media = new Media([
            'disk' => 's3',
            'visibility' => 'private',
            'path' => $stored->path,
            'original_name' => $stored->originalName,
            'mime' => $stored->mime,
            'ext' => $stored->extension,
        ]);

        $url = $storage->temporaryUrl($media);

        $this->assertNotNull($url);
        $this->assertStringContainsString($stored->path, $url);
    }
}
