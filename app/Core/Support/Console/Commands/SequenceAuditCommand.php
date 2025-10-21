<?php

namespace App\Core\Support\Console\Commands;

use App\Core\Support\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SequenceAuditCommand extends Command
{
    protected $signature = 'webileri:sequence:audit {--company=} {--key=} {--path=docs/Numbering_Audit.md}';

    protected $description = 'Audit numbering sequences and highlight drifts between counters and persisted documents.';

    public function handle(): int
    {
        $definitions = Config::get('numbering.defaults', []);

        if ($definitions === []) {
            $this->warn('No numbering defaults configured.');

            return self::SUCCESS;
        }

        $companyFilter = $this->option('company');
        $keyFilter = $this->option('key');

        $rows = [];
        $logPayload = [];

        Company::query()
            ->when($companyFilter, fn ($q) => $q->where('id', $companyFilter))
            ->orderBy('id')
            ->chunkById(50, function ($companies) use (&$rows, &$logPayload, $definitions, $keyFilter): void {
                foreach ($companies as $company) {
                    foreach ($definitions as $key => $definition) {
                        if ($keyFilter && Str::upper($keyFilter) !== $key) {
                            continue;
                        }

                        $sequence = DB::table('sequences')
                            ->where('company_id', $company->id)
                            ->where('key', $key)
                            ->whereNull('scope')
                            ->first();

                        if (! $sequence) {
                            $rows[] = [
                                'company' => $company->name,
                                'key' => $key,
                                'current' => 0,
                                'max_serial' => 0,
                                'delta' => 0,
                                'status' => 'missing sequence',
                            ];

                            $logPayload[] = [
                                'company_id' => $company->id,
                                'key' => $key,
                                'issue' => 'sequence_missing',
                            ];

                            continue;
                        }

                        $table = Arr::get($definition, 'table');
                        $column = Arr::get($definition, 'column');

                        if (! $table || ! $column) {
                            continue;
                        }

                        $documentQuery = DB::table($table)
                            ->where('company_id', $company->id)
                            ->whereNotNull($column);

                        if ($sequence->last_reset_at) {
                            $documentQuery->where('created_at', '>=', $sequence->last_reset_at);
                        }

                        $numbers = $documentQuery->pluck($column);

                        $maxSerial = 0;
                        $duplicates = [];
                        $seen = [];

                        foreach ($numbers as $number) {
                            if (! is_string($number) || $number === '') {
                                continue;
                            }

                            if (isset($seen[$number])) {
                                $duplicates[$number] = true;
                            }

                            $seen[$number] = true;

                            if (preg_match('/(\d+)$/', $number, $matches)) {
                                $serial = (int) $matches[1];

                                if ($serial > $maxSerial) {
                                    $maxSerial = $serial;
                                }
                            }
                        }

                        $delta = ((int) $sequence->current) - $maxSerial;

                        $status = $delta === 0 ? 'ok' : ($delta < 0 ? 'ahead of data' : 'needs bump');

                        if ($duplicates !== []) {
                            $status .= ' / duplicates';
                        }

                        $rows[] = [
                            'company' => $company->name,
                            'key' => $key,
                            'current' => (int) $sequence->current,
                            'max_serial' => $maxSerial,
                            'delta' => $delta,
                            'status' => $status,
                        ];

                        $logPayload[] = [
                            'company_id' => $company->id,
                            'key' => $key,
                            'current' => (int) $sequence->current,
                            'max_serial' => $maxSerial,
                            'delta' => $delta,
                            'duplicates' => array_keys($duplicates),
                        ];
                    }
                }
            });

        $path = base_path($this->option('path'));
        $content = "# Numbering Audit\n\nGenerated at " . Carbon::now()->toDateTimeString() . "\n\n";
        $content .= "| Company | Key | Current Counter | Max Serial | Delta | Status |\n";
        $content .= "| --- | --- | ---:| ---:| ---:| --- |\n";

        foreach ($rows as $row) {
            $content .= sprintf(
                "| %s | %s | %d | %d | %d | %s |\n",
                $row['company'],
                $row['key'],
                $row['current'],
                $row['max_serial'],
                $row['delta'],
                $row['status']
            );
        }

        file_put_contents($path, $content);
        Log::info('Sequence audit executed', $logPayload);

        $this->info(sprintf('Audit rows written: %d (path: %s)', count($rows), $path));

        return self::SUCCESS;
    }
}
