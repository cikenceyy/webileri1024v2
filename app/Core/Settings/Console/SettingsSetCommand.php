<?php

namespace App\Core\Settings\Console;

use App\Core\Settings\SettingsRepository;
use Illuminate\Console\Command;
use InvalidArgumentException;

/**
 * CLI üzerinden ayar anahtarlarını günceller.
 */
class SettingsSetCommand extends Command
{
    protected $signature = 'settings:set
        {--company= : Şirket kimliği}
        {--key= : Ayar anahtarı}
        {--type=string : Değer tipi (bool|int|string|json|email)}
        {--value= : Ayar değeri}
    ';

    protected $description = 'Belirtilen şirket için ayar değerini günceller.';

    public function handle(SettingsRepository $repository): int
    {
        $companyId = (int) $this->option('company');
        $key = (string) $this->option('key');
        $type = (string) $this->option('type', 'string');
        $value = $this->option('value');

        if ($companyId <= 0 || $key === '') {
            $this->error('company ve key parametreleri zorunludur.');

            return self::FAILURE;
        }

        try {
            $repository->set($companyId, $key, $value, $type);
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('%s anahtarı güncellendi.', $key));

        return self::SUCCESS;
    }
}
