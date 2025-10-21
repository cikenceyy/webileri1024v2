<?php

use App\Core\Support\Models\Company;
use App\Core\Tenancy\Middleware\IdentifyTenant;
use App\Modules\Inventory\Domain\Models\Product;
use Illuminate\Http\Request;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('tenant middleware resolves company and shares it across the request lifecycle', function () {
    $company = Company::factory()->create(['domain' => 'tenant.test']);

    $middleware = new IdentifyTenant();
    $request = Request::create('https://tenant.test/dashboard', 'GET');

    $response = $middleware->handle($request, fn ($req) => response()->json([
        'company_id' => $req->attributes->get('company_id'),
        'shared_company' => app('company')->id ?? null,
    ]));

    expect($response->getStatusCode())->toBe(200);

    tap(json_decode($response->getContent(), true), function (array $payload) use ($company) {
        expect($payload['company_id'])->toBe($company->id);
        expect($payload['shared_company'])->toBe($company->id);
    });
});

test('belongs to company trait assigns company id and scopes queries', function () {
    $companyA = Company::factory()->create(['domain' => 'a.test']);
    $companyB = Company::factory()->create(['domain' => 'b.test']);

    app()->instance('company', $companyA);
    request()->attributes->set('company_id', $companyA->id);

    $product = Product::query()->create([
        'sku' => 'PRD-001',
        'name' => 'Scoped Product',
        'price' => 10,
        'unit' => 'pcs',
        'status' => 'active',
    ]);

    expect($product->company_id)->toBe($companyA->id);

    app()->instance('company', $companyB);
    request()->attributes->set('company_id', $companyB->id);

    expect(Product::query()->whereKey($product->id)->exists())->toBeFalse();
    expect(Product::withoutGlobalScopes()->whereKey($product->id)->exists())->toBeTrue();
});

test('tenant middleware gracefully aborts when company cannot be resolved', function () {
    $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

    $middleware = new IdentifyTenant();
    $request = Request::create('https://missing.test/dashboard', 'GET');

    $middleware->handle($request, fn () => response()->noContent());
});
