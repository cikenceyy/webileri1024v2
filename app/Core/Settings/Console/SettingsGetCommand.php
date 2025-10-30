<?php

namespace App\Core\Settings\Console;

use App\Core\Settings\SettingsRepository;
use Illuminate\Console\Command;

/**
 * CLI üzerinden tek bir ayar anahtarını okur ve çıktısını yazar.
 */
class SettingsGetCommand extends Command
{
    protected $signature = 'settings:get {--company= : Şirket kimliği} {--key= : Ayar anahtarı}';

    protected $description = 'Belirtilen şirket için ayar değerini görüntüler.';

    public function handle(SettingsRepository $repository): int
    {
        $companyId = (int) $this->option('company');
        $key = (string) $this->option('key');

        if ($companyId <= 0 || $key === '') {
            $this->error('company ve key parametreleri zorunludur.');

            return self::FAILURE;
        }

        $value = $repository->get($companyId, $key);

        $this->line(sprintf('%s => %s', $key, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)));

        return self::SUCCESS;
    }
}
