<?php

namespace App\Cms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WarmPageCache implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ?string $url, protected ?string $locale = null)
    {
    }

    public function handle(): void
    {
        if (!$this->url) {
            return;
        }

        try {
            Http::timeout(5)->withHeaders([
                'X-CMS-Warmup' => 'true',
                'Accept-Language' => $this->locale ?? 'tr',
            ])->get($this->url . (Str::contains($this->url, '?') ? '&' : '?') . 'cache_warm=1');
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

}
