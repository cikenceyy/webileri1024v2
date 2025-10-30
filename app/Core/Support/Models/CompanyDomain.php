<?php

namespace App\Core\Support\Models;

use App\Core\Tenancy\DomainCacheManager;
use App\Core\Tenancy\Support\DomainNormalizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kiracı domain kayıtlarını temsil eder.
 * Host değerleri kaydedilirken küçük harfe çevrilir ve olaylarda cache temizliği tetiklenir.
 */
class CompanyDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'host',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'bool',
    ];

    protected static function booted(): void
    {
        static::saving(function (CompanyDomain $domain): void {
            if ($domain->is_primary) {
                CompanyDomain::query()
                    ->where('company_id', $domain->company_id)
                    ->where('id', '!=', $domain->id)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        static::saved(function (CompanyDomain $domain): void {
            app(DomainCacheManager::class)->flushForCompany((int) $domain->company_id, [$domain->host], [
                'reason' => 'domain.saved',
                'domain_id' => $domain->id,
            ]);
        });

        static::deleted(function (CompanyDomain $domain): void {
            app(DomainCacheManager::class)->flushForCompany((int) $domain->company_id, [$domain->host], [
                'reason' => 'domain.deleted',
                'domain_id' => $domain->id,
            ]);
        });
    }

    /**
     * Host değerini küçük harfe ve trimlenmiş hale getirir.
     */
    protected function host(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : DomainNormalizer::normalize($value)
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
