<?php

namespace App\Core\Bulk;

use App\Core\Bulk\Jobs\RunBulkActionJob;
use App\Core\Bulk\Models\BulkJob;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Konsol ekranlarından tetiklenen toplu işlemleri kuyruklar ve takip eder.
 *
 * Maliyet Notu: Her kullanıcı için eşzamanlı en fazla N iş (varsayılan 2)
 * çalıştırılır; iş kayıtları cache ile 60 sn tutulur ve duplicate tetikler
 * idempotency anahtarıyla engellenir.
 */
class BulkActionService
{
    public function dispatch(
        int $companyId,
        ?Authenticatable $user,
        string $module,
        string $action,
        array $params = []
    ): BulkJob {
        $userId = $user?->getAuthIdentifier();
        $idempotencyKey = $this->resolveIdempotencyKey($companyId, $userId, $module, $action, $params);

        $existing = BulkJob::query()
            ->where('company_id', $companyId)
            ->where('idempotency_key', $idempotencyKey)
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $limit = (int) Config::get('consolekit.bulk.max_concurrent', 2);
        $runningCount = BulkJob::query()
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'running'])
            ->count();

        if ($runningCount >= $limit) {
            throw new RuntimeException('Aynı anda çalıştırılabilecek toplu işlem sınırına ulaşıldı.');
        }

        $job = BulkJob::query()->create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'module' => $module,
            'action' => $action,
            'params' => $params,
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey,
            'items_total' => (int) Arr::get($params, 'items_total', 0),
        ]);

        Bus::dispatch(new RunBulkActionJob($job->id));

        return $job;
    }

    /**
     * @return array<int, BulkJob>
     */
    public function latestForUser(int $companyId, ?Authenticatable $user, int $limit = 10): array
    {
        return BulkJob::query()
            ->where('company_id', $companyId)
            ->when($user, fn ($query) => $query->where('user_id', $user?->getAuthIdentifier()))
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->all();
    }

    private function resolveIdempotencyKey(
        int $companyId,
        ?int $userId,
        string $module,
        string $action,
        array $params
    ): string {
        $payload = json_encode([
            'company' => $companyId,
            'user' => $userId,
            'module' => $module,
            'action' => $action,
            'params' => $params,
        ]);

        return Str::limit(hash('sha1', (string) $payload), 64, '');
    }
}
