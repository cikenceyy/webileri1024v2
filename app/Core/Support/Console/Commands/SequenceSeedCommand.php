<?php

namespace App\Core\Support\Console\Commands;

use App\Core\Support\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SequenceSeedCommand extends Command
{
    protected $signature = 'webileri:sequence:seed {--force : Run outside local environments}';

    protected $description = 'Create default numbering sequences for each tenant.';

    public function handle(): int
    {
        $shouldRun = App::environment('local') || Str::contains((string) config('app.url'), 'localhost') || $this->option('force');

        if (! $shouldRun) {
            $this->warn('Skipping sequence seeding outside local environments. Use --force to override.');

            return self::SUCCESS;
        }

        $definitions = Config::get('numbering.defaults', []);

        if ($definitions === []) {
            $this->warn('No numbering defaults defined.');

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;

        Company::query()->chunkById(100, function ($companies) use (&$created, &$updated, $definitions): void {
            foreach ($companies as $company) {
                foreach ($definitions as $key => $definition) {
                    $existing = DB::table('sequences')
                        ->where('company_id', $company->id)
                        ->where('key', $key)
                        ->whereNull('scope')
                        ->first();

                    if (! $existing) {
                        DB::table('sequences')->insert([
                            'company_id' => $company->id,
                            'key' => $key,
                            'current' => 0,
                            'prefix' => Arr::get($definition, 'prefix', $key),
                            'padding' => Arr::get($definition, 'padding', 4),
                            'reset_period' => Arr::get($definition, 'reset_period', 'yearly'),
                            'scope' => null,
                            'last_reset_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $created++;

                        continue;
                    }

                    $changes = [];

                    foreach (['prefix', 'padding', 'reset_period'] as $attribute) {
                        $newValue = Arr::get($definition, $attribute, $existing->{$attribute});

                        if ($existing->{$attribute} !== $newValue) {
                            $changes[$attribute] = $newValue;
                        }
                    }

                    if ($changes !== []) {
                        $changes['updated_at'] = now();

                        DB::table('sequences')->where('id', $existing->id)->update($changes);
                        $updated++;
                    }
                }
            }
        });

        $this->info(sprintf('Sequences created: %d, updated: %d', $created, $updated));
        Log::info('Sequence seed executed', ['created' => $created, 'updated' => $updated]);

        return self::SUCCESS;
    }
}
