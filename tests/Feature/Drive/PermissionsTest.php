<?php

namespace Tests\Feature\Drive;

use App\Core\Support\Models\Company;
use App\Models\User;
use App\Modules\Drive\Domain\Models\Media;
use App\Modules\Drive\Policies\MediaPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_users_can_view_and_delete_their_media(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $other = User::factory()->create();

        $media = Media::query()->create([
            'company_id' => $company->id,
            'module' => 'cms',
            'category' => 'documents',
            'disk' => 'local',
            'visibility' => 'private',
            'path' => 'companies/' . $company->id . '/drive/cms/documents/example.pdf',
            'original_name' => 'example.pdf',
            'mime' => 'application/pdf',
            'ext' => 'pdf',
            'size' => 512,
            'is_important' => false,
            'uploaded_by' => $user->id,
        ]);

        app()->instance('company', $company);

        $policy = app(MediaPolicy::class);

        $this->assertTrue($policy->view($user, $media));
        $this->assertTrue($policy->delete($user, $media));
        $this->assertFalse($policy->view($other, $media));
        $this->assertFalse($policy->delete($other, $media));
    }
}
