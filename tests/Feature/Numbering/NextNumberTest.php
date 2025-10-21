<?php

namespace Tests\Feature\Numbering;

use App\Core\Bus\Actions\NextNumber;
use App\Core\Support\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NextNumberTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        parent::tearDown();
        Carbon::setTestNow();
    }

    public function test_it_generates_yearly_sequence_numbers(): void
    {
        $company = Company::factory()->create();
        $action = app(NextNumber::class);

        Carbon::setTestNow(Carbon::create(2025, 1, 15));
        $first = $action($company->id, 'INV');
        $second = $action($company->id, 'INV');

        Carbon::setTestNow(Carbon::create(2026, 1, 5));
        $third = $action($company->id, 'INV');

        $this->assertSame('INV-2025-0001', $first);
        $this->assertSame('INV-2025-0002', $second);
        $this->assertSame('INV-2026-0001', $third);
    }

    public function test_sequence_rolls_back_on_transaction_failure(): void
    {
        $company = Company::factory()->create();
        $action = app(NextNumber::class);

        Carbon::setTestNow(Carbon::create(2025, 2, 1));

        DB::beginTransaction();
        $number = $action($company->id, 'SO');
        DB::rollBack();

        $again = $action($company->id, 'SO');

        $this->assertSame($number, $again);
    }

    public function test_idempotency_returns_same_number(): void
    {
        $company = Company::factory()->create();
        $action = app(NextNumber::class);

        Carbon::setTestNow(Carbon::create(2025, 3, 10));

        $first = $action($company->id, 'PO', ['idempotency_key' => 'order-123']);
        $second = $action($company->id, 'PO', ['idempotency_key' => 'order-123']);

        $this->assertSame($first, $second);
        $this->assertSame('PO-2025-0001', $first);
    }

    public function test_monthly_reset_applies_to_work_orders(): void
    {
        $company = Company::factory()->create();
        $action = app(NextNumber::class);

        Carbon::setTestNow(Carbon::create(2025, 4, 30));
        $april = $action($company->id, 'WO', ['reset_period' => 'monthly']);

        Carbon::setTestNow(Carbon::create(2025, 5, 1));
        $may = $action($company->id, 'WO', ['reset_period' => 'monthly']);

        $this->assertSame('WO-202504-0001', $april);
        $this->assertSame('WO-202505-0001', $may);
    }
}
