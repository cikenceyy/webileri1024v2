<?php

namespace App\Modules\Drive\Domain\Observers;

use App\Core\Cache\InvalidationService;
use App\Modules\Drive\Domain\Models\Media;

/**
 * Drive medya kayıtlarında değişiklik olduğunda ilgili cache taglerini temizler.
 */
class MediaObserver
{
    public function __construct(private readonly InvalidationService $cache)
    {
    }

    public function created(Media $media): void
    {
        $this->flush($media, 'created');
    }

    public function updated(Media $media): void
    {
        $this->flush($media, 'updated');
    }

    public function deleted(Media $media): void
    {
        $this->flush($media, $media->isForceDeleting() ? 'force-deleted' : 'deleted');
    }

    private function flush(Media $media, string $event): void
    {
        if (! $media->company_id) {
            return;
        }

        $tags = array_filter([
            'drive',
            'drive:stats',
            'drive:recent',
            $media->category ? sprintf('drive:folder:%s', $media->category) : null,
            $media->module ? sprintf('drive:module:%s', $media->module) : null,
        ]);

        $this->cache->flushTenant((int) $media->company_id, $tags, [
            'reason' => 'drive.media.' . $event,
            'media_id' => $media->id,
        ]);
    }
}
