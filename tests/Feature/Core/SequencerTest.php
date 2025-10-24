<?php

namespace Tests\Feature\Core;

use App\Core\Domain\Sequencing\Sequencer;
use App\Core\Support\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SequencerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_unique_numbers_for_same_company_and_key(): void
    {
        $company = Company::factory()->create();
        $sequencer = app(Sequencer::class);

        $numbers = collect(range(1, 5))->map(function () use ($sequencer, $company) {
            return $sequencer->next($company->id, 'invoice', 'INV', 6, 'never');
        });

        $this->assertCount(5, $numbers->unique());
        $this->assertTrue($numbers->every(fn ($value) => str_starts_with($value, 'INV')));
    }

    public function test_it_resets_each_year_when_policy_is_yearly(): void
    {
        $company = Company::factory()->create();
        $sequencer = app(Sequencer::class);

        Carbon::setTestNow(Carbon::create(2024, 12, 31, 23, 59));
        $first = $sequencer->next($company->id, 'invoice', 'INV', 6, 'yearly');
        $this->assertSame('INV000001', $first);

        Carbon::setTestNow(Carbon::create(2025, 1, 1, 0, 1));
        $second = $sequencer->next($company->id, 'invoice', 'INV', 6, 'yearly');
        $third = $sequencer->next($company->id, 'invoice', 'INV', 6, 'yearly');

        $this->assertSame('INV000001', $second);
        $this->assertSame('INV000002', $third);

        Carbon::setTestNow();
    }
}
