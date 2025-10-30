<?php

namespace App\Core\Console\Commands;

use App\Core\Cache\TenantCacheManager;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use function currentCompanyId;

/**
 * Tenant bazlı önbellek temizliğini CLI üzerinden başlatır.
 */
class ProjectCacheFlushCommand extends Command
{
    protected $signature = 'project:cache:flush {--tenant= : Firma kimliği (zorunlu)} {--tags= : Virgülle ayrılmış tag listesi} {--hard : Tüm tenant verilerini temizle}';

    protected $description = 'Belirtilen tenant için önbellek temizleme işlemini çalıştırır.';

    public function __construct(private readonly TenantCacheManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantOption = $this->option('tenant');

        if ($tenantOption === null || $tenantOption === '') {
            $current = currentCompanyId();
            if (! $current) {
                $this->components->error('Tenant parametresi zorunludur.');

                return self::FAILURE;
            }
            $tenantOption = $current;
        }

        if (is_array($tenantOption)) {
            $tenantOption = Arr::first($tenantOption);
        }

        if (! is_numeric($tenantOption)) {
            $this->components->error('Tenant parametresi sayısal olmalıdır.');

            return self::FAILURE;
        }

        $tagsOption = (string) $this->option('tags');
        $tags = $tagsOption !== '' ? array_values(array_unique(array_filter(array_map('trim', explode(',', $tagsOption))))) : [];
        $hard = (bool) $this->option('hard');
        $tenantId = (int) $tenantOption;

        $this->components->task(
            sprintf('Firma #%d için önbellek temizliği', $tenantId),
            function () use ($tenantId, $tags, $hard): bool {
                $this->manager->flushTenant($tenantId, $tags, $hard, [
                    'reason' => 'console.flush',
                ]);

                return true;
            }
        );

        $this->components->info('Temizlik tamamlandı.');

        return self::SUCCESS;
    }
}
