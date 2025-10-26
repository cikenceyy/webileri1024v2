<?php

namespace Tests\Feature\Drive;

use App\Core\Support\Models\Company;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Http\Controllers\MediaController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ListingFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_media_by_search_extension_and_module(): void
    {
        $company = Company::factory()->create();

        Media::query()->create([
            'company_id' => $company->id,
            'module' => 'cms',
            'category' => 'documents',
            'disk' => 'local',
            'visibility' => 'private',
            'path' => 'companies/' . $company->id . '/drive/cms/documents/file-one.pdf',
            'original_name' => 'Finans Raporu 2024.pdf',
            'mime' => 'application/pdf',
            'ext' => 'pdf',
            'size' => 1200,
            'is_important' => false,
        ]);

        Media::query()->create([
            'company_id' => $company->id,
            'module' => 'marketing',
            'category' => 'media',
            'disk' => 'local',
            'visibility' => 'private',
            'path' => 'companies/' . $company->id . '/drive/marketing/media/file-two.png',
            'original_name' => 'Kampanya Banner.png',
            'mime' => 'image/png',
            'ext' => 'png',
            'size' => 800,
            'is_important' => false,
        ]);

        $controller = app(MediaController::class);
        $method = new \ReflectionMethod(MediaController::class, 'applyFilters');
        $method->setAccessible(true);

        $request = Request::create('/admin/drive', 'GET', [
            'q' => 'Raporu',
            'module' => 'cms',
            'ext' => 'pdf',
        ]);

        $query = Media::query()->where('company_id', $company->id);
        $result = $method->invoke($controller, $query, $request)->get();

        $this->assertCount(1, $result);
        $this->assertSame('Finans Raporu 2024.pdf', $result->first()->original_name);
    }
}
