<?php

use App\Core\Support\Models\Company;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Models\User;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    if (! class_exists(Permission::class)) {
        $this->markTestSkipped('Spatie permission package is required.');
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->withoutMiddleware([IdentifyTenant::class]);
    Storage::fake('public');
});

function driveUserWithPermissions(array $permissions): array
{
    $company = Company::factory()->create();
    app()->instance('company', $company);
    view()->share('company', $company);

    $user = User::factory()->create(['company_id' => $company->id]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    $user->syncPermissions($permissions);
    app(PermissionRegistrar::class)->setPermissionsTeamId(null);

    return [$company, $user];
}

function createCompanyMedia(Company $company, array $attributes = []): Media
{
    app()->instance('company', $company);
    view()->share('company', $company);

    $path = $attributes['path'] ?? sprintf('companies/%s/drive/documents/%s.pdf', $company->id, uniqid('file_', true));
    Storage::disk('public')->put($path, 'demo');

    return Media::create(array_merge([
        'company_id' => $company->id,
        'category' => Media::CATEGORY_DOCUMENTS,
        'disk' => 'public',
        'path' => $path,
        'original_name' => $attributes['original_name'] ?? 'sample.pdf',
        'mime' => $attributes['mime'] ?? 'application/pdf',
        'ext' => $attributes['ext'] ?? 'pdf',
        'size' => $attributes['size'] ?? 512,
        'uploaded_by' => $attributes['uploaded_by'] ?? null,
    ], $attributes));
}

test('authorized users can upload media files', function (): void {
    [$company, $user] = driveUserWithPermissions(['drive.file.upload']);

    $file = UploadedFile::fake()->create('report.pdf', 128, 'application/pdf');

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.drive.media.store'), [
            'category' => Media::CATEGORY_DOCUMENTS,
            'file' => $file,
        ]);

    $response->assertCreated()
        ->assertJsonPath('media.original_name', 'report.pdf');

    $media = Media::first();
    expect($media)->not->toBeNull()
        ->and($media->company_id)->toBe($company->id)
        ->and(Storage::disk('public')->exists($media->path))->toBeTrue();
});

test('upload is forbidden without permission', function (): void {
    [, $user] = driveUserWithPermissions([]);

    $file = UploadedFile::fake()->create('restricted.pdf', 64, 'application/pdf');

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->post(route('admin.drive.media.store'), [
            'category' => Media::CATEGORY_DOCUMENTS,
            'file' => $file,
        ]);

    $response->assertForbidden();
    expect(Media::count())->toBe(0);
});

test('media actions are scoped to the active tenant company', function (): void {
    [$companyA, $user] = driveUserWithPermissions(['drive.file.mark']);

    $mediaA = createCompanyMedia($companyA, ['is_important' => false]);

    $companyB = Company::factory()->create();
    $mediaB = createCompanyMedia($companyB, ['is_important' => false]);

    app()->instance('company', $companyA);
    view()->share('company', $companyA);

    $toggleResponse = $this->actingAs($user)
        ->postJson(route('admin.drive.media.toggle_important', ['media' => $mediaA->id]));

    $toggleResponse->assertOk()
        ->assertJsonPath('is_important', true);

    expect($mediaA->fresh()->is_important)->toBeTrue();

    $blockedResponse = $this->actingAs($user)
        ->postJson(route('admin.drive.media.toggle_important', ['media' => $mediaB->id]));

    $blockedResponse->assertNotFound();
    expect($mediaB->fresh()->is_important)->toBeFalse();
});
