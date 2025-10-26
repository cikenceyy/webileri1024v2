<?php

namespace Tests\Feature\Drive;

use App\Core\Support\Models\Company;
use App\Modules\Drive\Domain\DriveStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadLocalTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_files_on_the_local_disk_with_expected_path(): void
    {
        Storage::fake('local');
        Config::set('drive.disk', 'local');
        Config::set('filesystems.default', 'local');

        $company = Company::factory()->create();
        $storage = app(DriveStorage::class);
        $file = UploadedFile::fake()->create('sozlesme.pdf', 120, 'application/pdf');

        $stored = $storage->put($file, $company->id, 'cms', 'documents');

        $this->assertSame('local', $stored->disk);
        $this->assertStringContainsString((string) $company->id, $stored->path);
        $this->assertStringContainsString('/documents/', $stored->path);
        Storage::disk('local')->assertExists($stored->path);
        $this->assertSame('application/pdf', $stored->mime);
        $this->assertSame('pdf', $stored->extension);
    }
}
