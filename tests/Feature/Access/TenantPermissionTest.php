<?php

use App\Core\Support\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    if (! class_exists(Role::class)) {
        $this->markTestSkipped('Spatie permission package is not installed.');
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

test('roles and permissions are isolated per tenant company', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $userA = User::factory()->create(['company_id' => $companyA->id]);
    $userB = User::factory()->create(['company_id' => $companyB->id]);

    $permission = Permission::findOrCreate('marketing.lead.update', 'web');
    $role = Role::findOrCreate('patron', 'web');
    $role->syncPermissions([$permission]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyA->id);
    $userA->assignRole('patron');

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyA->id);
    expect($userA->can('marketing.lead.update'))->toBeTrue();

    app(PermissionRegistrar::class)->setPermissionsTeamId($companyB->id);
    expect($userB->can('marketing.lead.update'))->toBeFalse();
});

test('stajyer role is limited to read-only permissions', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $view = Permission::findOrCreate('inventory.view', 'web');
    $update = Permission::findOrCreate('inventory.product.update', 'web');

    $role = Role::findOrCreate('stajyer', 'web');
    $role->syncPermissions([$view]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('stajyer');

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    expect($user->can('inventory.view'))->toBeTrue();
    expect($user->can('inventory.product.update'))->toBeFalse();
});

test('biz role bypasses granular permission checks via gate before hook', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    Role::findOrCreate('biz', 'web');

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
    $user->assignRole('biz');

    app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

    expect($user->can('finance.invoice.post'))->toBeTrue();
    expect(Gate::forUser($user)->allows('some.arbitrary.permission'))->toBeTrue();
});
