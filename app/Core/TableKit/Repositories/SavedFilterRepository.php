<?php

namespace App\Core\TableKit\Repositories;

use App\Core\TableKit\Models\TablekitFilter;
use Illuminate\Support\Facades\DB;

/**
 * TableKit filtre kayıtlarını yönetmek için yardımcı repository.
 * Amaç: Varsayılan filtre atama ve CRUD operasyonlarını merkezileştirmek.
 */
class SavedFilterRepository
{
    /**
     * Verilen parametrelerle yeni filtre oluşturur.
     */
    public function create(int $companyId, int $userId, string $tableKey, string $name, array $payload, bool $isDefault = false): TablekitFilter
    {
        return DB::transaction(function () use ($companyId, $userId, $tableKey, $name, $payload, $isDefault) {
            if ($isDefault) {
                TablekitFilter::query()
                    ->where('company_id', $companyId)
                    ->where('user_id', $userId)
                    ->where('table_key', $tableKey)
                    ->update(['is_default' => false]);
            }

            return TablekitFilter::query()->create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'table_key' => $tableKey,
                'name' => $name,
                'payload' => $payload,
                'is_default' => $isDefault,
            ]);
        });
    }

    /**
     * Varsayılan filtreyi günceller.
     */
    public function markDefault(TablekitFilter $filter): void
    {
        DB::transaction(function () use ($filter): void {
            TablekitFilter::query()
                ->where('company_id', $filter->company_id)
                ->where('user_id', $filter->user_id)
                ->where('table_key', $filter->table_key)
                ->update(['is_default' => false]);

            $filter->update(['is_default' => true]);
        });
    }
}
