<?php

namespace App\Core\Console\Commands;

use App\Core\Cache\TenantCacheManager;
use App\Core\Support\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use function currentCompanyId;

/**
 * Isıtma komutunu CLI üzerinden tetikleyerek sıcak veri kümelerini hazırlar.
 */
class ProjectCacheWarmCommand extends Command
{
    protected $signature = 'project:cache:warm {--tenant=* : Firma kimliği (* tüm firmalar)} {--entities= : Virgülle ayrılmış ısıtma listesi}';

    protected $description = 'Belirtilen tenant için önbellek ısıtma rutinlerini çalıştırır.';

    public function __construct(private readonly TenantCacheManager $manager)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $tenantOption = $this->option('tenant');
        $entitiesOption = (string) $this->option('entities');
        $entities = $entitiesOption !== '' ? array_values(array_unique(array_filter(array_map('trim', explode(',', $entitiesOption))))) : [];

        $companyIds = $this->resolveCompanyIds($tenantOption);

        if ($companyIds === []) {
            $this->warn('Isıtılacak firma bulunamadı.');

            return self::SUCCESS;
        }

        foreach ($companyIds as $companyId) {
            $executed = [];

            $this->components->task(
                sprintf('Firma #%d için ısıtma', $companyId),
                function () use ($companyId, $entities, &$executed): bool {
                    $executed = $this->manager->warmTenant($companyId, $entities);

                    return true;
                }
            );

            if ($executed === []) {
                $this->components->warn('Çalıştırılacak ısıtıcı bulunamadı.');
            } else {
                $this->components->info('Isıtılan varlıklar: ' . implode(', ', $executed));
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  mixed  $tenantOption
     * @return array<int, int>
     */
    private function resolveCompanyIds(mixed $tenantOption): array
    {
        if ($tenantOption === '*' || $tenantOption === ["*"]) {
            return Company::query()->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        }

        if (is_array($tenantOption)) {
            $tenantOption = Arr::first($tenantOption);
        }

        if ($tenantOption === null || $tenantOption === '') {
            $current = currentCompanyId();

            if ($current) {
            return [$current];
            }

            $fallback = Company::query()->orderBy('id')->value('id');

            return $fallback ? [(int) $fallback] : [];
        }

        if (! is_numeric($tenantOption)) {
            $this->components->error('Tenant parametresi sayısal olmalıdır.');

            return [];
        }

        return [(int) $tenantOption];
    }
}
