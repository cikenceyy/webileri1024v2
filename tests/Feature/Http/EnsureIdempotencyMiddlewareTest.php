<?php

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureIdempotencyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['idempotency.enabled' => true]);
        config(['features.idempotency.enforced' => true]);
        Cache::store('array')->flush();
        Cache::store('array')->put('idempotency:test-counter', 0);

        Route::middleware(['web', 'idempotency'])->post('/test-idempotent', function () {
            $count = Cache::store('array')->increment('idempotency:test-counter');

            return response()->json(['count' => $count], 201);
        });
    }

    public function test_replaying_same_idempotency_key_returns_cached_response(): void
    {
        $headers = ['Idempotency-Key' => 'abc-123'];

        $first = $this->postJson('/test-idempotent', [], $headers);
        $first->assertStatus(201)->assertJson(['count' => 1]);

        $second = $this->postJson('/test-idempotent', [], $headers);
        $second->assertStatus(201)->assertJson(['count' => 1]);
        $this->assertSame(1, Cache::store('array')->get('idempotency:test-counter'));
        $this->assertSame('true', $second->headers->get('Idempotency-Replayed'));
    }

    public function test_missing_idempotency_key_returns_bad_request(): void
    {
        $response = $this->postJson('/test-idempotent', []);

        $response->assertStatus(400);
    }
}
