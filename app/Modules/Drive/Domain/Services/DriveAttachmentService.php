<?php

namespace App\Modules\Drive\Domain\Services;

use App\Core\Cache\InvalidationService;
use App\Core\Cache\Keys;
use App\Modules\Drive\Domain\Models\DriveAttachment;
use App\Modules\Drive\Domain\Models\Media;
use Illuminate\Support\Collection;

/**
 * Drive dosyalarını modül kayıtlarına iliştirmek için ortak servis.
 * Maliyet Notu: Listeleme sorguları cache hot (60 sn), ilişkilendirme sonrası
 * drive:* etiketleri temizlenir.
 */
class DriveAttachmentService
{
    public function __construct(private readonly InvalidationService $cacheInvalidation)
    {
    }

    /**
     * Belirli bir kayıt için Drive eklerini listeler.
     */
    public function list(string $attachableType, int $attachableId): Collection
    {
        $companyId = (int) (currentCompanyId() ?? 0);

        return DriveAttachment::query()
            ->where('company_id', $companyId)
            ->where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->with('media')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Drive deposundan dosya seçip kayda iliştirir.
     */
    public function attach(string $attachableType, int $attachableId, Media $media, array $meta = []): DriveAttachment
    {
        $companyId = (int) (currentCompanyId() ?? 0);

        $attachment = DriveAttachment::updateOrCreate(
            [
                'company_id' => $companyId,
                'attachable_type' => $attachableType,
                'attachable_id' => $attachableId,
                'media_id' => $media->id,
            ],
            ['meta' => $meta]
        );

        $this->flushAttachmentCache($attachableType, $attachableId, $companyId);

        return $attachment;
    }

    /**
     * Kayda bağlı dosyayı kaldırır.
     */
    public function detach(string $attachableType, int $attachableId, int $mediaId): void
    {
        $companyId = (int) (currentCompanyId() ?? 0);

        DriveAttachment::query()
            ->where('company_id', $companyId)
            ->where('attachable_type', $attachableType)
            ->where('attachable_id', $attachableId)
            ->where('media_id', $mediaId)
            ->delete();

        $this->flushAttachmentCache($attachableType, $attachableId, $companyId);
    }

    /**
     * Drive modalı için cache anahtarını döndürür.
     */
    public function cacheKey(string $context): string
    {
        return Keys::forTenant((int) (currentCompanyId() ?? 0), ['drive', 'attachments', $context]);
    }

    private function flushAttachmentCache(string $attachableType, int $attachableId, int $companyId): void
    {
        $tag = sprintf('drive:%d:%s:%d', $companyId, $attachableType, $attachableId);
        $this->cacheInvalidation->flushTags([$tag, 'drive']);
    }
}
