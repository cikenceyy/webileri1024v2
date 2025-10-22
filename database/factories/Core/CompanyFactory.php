<?php

namespace Database\Factories\Core;

use App\Core\Support\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $domain = $this->faker->unique()->domainName();

        return [
            'name' => $this->faker->company(),
            'domain' => $domain,
            'theme_color' => $this->faker->safeHexColor(),
            'drive_storage_limit_bytes' => 1_073_741_824,
        ];
    }
}
