<?php

namespace App\Core\Cache;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;

/**
 * Önbellek ısıtma/temizlik aksiyonlarını dosya tabanlı olarak kayıt altına alır.
 * Admin paneli bu sınıftan son işlemleri ve zaman damgalarını okur.
 */
class CacheEventLogger
{
    private const DIRECTORY = 'cache';
    private const EVENTS_FILE = 'cache/events.log';
    private const META_FILE = 'cache/meta.json';

    public function __construct(private readonly FilesystemFactory $filesystem)
    {
    }

    public function record(string $action, ?int $companyId, array $context = []): void
    {
        $timestamp = CarbonImmutable::now();

        $store = $context['store'] ?? config('cache.default');
        unset($context['store']);

        $payload = [
            'action' => $action,
            'company_id' => $companyId,
            'context' => $context,
            'store' => $store,
            'timestamp' => $timestamp->toIso8601String(),
        ];

        try {
            Log::channel('cache')->info(sprintf('cache.%s', $action), $payload);
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->appendEvent($payload);

        if ($companyId !== null) {
            $this->updateMeta($companyId, $action, $timestamp);
        }
    }

    /**
     * @return array<int, array{action:string,company_id:?int,context:array,store:string,timestamp:CarbonImmutable}>
     */
    public function getRecentEvents(int $limit = 20): array
    {
        try {
            $disk = $this->disk();

            if (! $disk->exists(self::EVENTS_FILE)) {
                return [];
            }

            $lines = array_filter(preg_split("/(?:\r?\n)+/", (string) $disk->get(self::EVENTS_FILE)) ?: []);
            $lines = array_slice($lines, -1 * $limit);

            $events = [];

            foreach ($lines as $line) {
                $data = json_decode($line, true);
                if (! is_array($data)) {
                    continue;
                }

                $events[] = [
                    'action' => (string) ($data['action'] ?? 'unknown'),
                    'company_id' => isset($data['company_id']) ? (int) $data['company_id'] : null,
                    'context' => is_array($data['context'] ?? null) ? $data['context'] : [],
                    'store' => (string) ($data['store'] ?? config('cache.default')),
                    'timestamp' => isset($data['timestamp'])
                        ? CarbonImmutable::parse($data['timestamp'])
                        : CarbonImmutable::now(),
                ];
            }

            return array_reverse($events);
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }
    }

    public function getMeta(int $companyId): array
    {
        $meta = $this->loadMeta();
        $record = $meta[$companyId] ?? [];

        return [
            'last_warm' => isset($record['last_warm'])
                ? CarbonImmutable::parse($record['last_warm'])
                : null,
            'last_flush' => isset($record['last_flush'])
                ? CarbonImmutable::parse($record['last_flush'])
                : null,
        ];
    }

    private function appendEvent(array $payload): void
    {
        try {
            $this->ensureDirectory();
            $disk = $this->disk();
            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

            if ($encoded === false) {
                return;
            }

            $path = self::EVENTS_FILE;

            if ($disk->exists($path)) {
                $disk->append($path, $encoded);
                $this->trimEvents($disk, $path, 200);
            } else {
                $disk->put($path, $encoded . PHP_EOL);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function updateMeta(int $companyId, string $action, CarbonImmutable $timestamp): void
    {
        try {
            $meta = $this->loadMeta();
            $meta[$companyId] ??= [];

            if ($action === 'warm') {
                $meta[$companyId]['last_warm'] = $timestamp->toIso8601String();
            }

            if ($action === 'flush') {
                $meta[$companyId]['last_flush'] = $timestamp->toIso8601String();
            }

            $this->ensureDirectory();
            $this->disk()->put(self::META_FILE, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function loadMeta(): array
    {
        try {
            $disk = $this->disk();

            if (! $disk->exists(self::META_FILE)) {
                return [];
            }

            $decoded = json_decode((string) $disk->get(self::META_FILE), true);

            return is_array($decoded) ? $decoded : [];
        } catch (\Throwable $exception) {
            report($exception);

            return [];
        }
    }

    private function trimEvents(FilesystemAdapter $disk, string $path, int $limit): void
    {
        $lines = array_filter(preg_split("/(?:\r?\n)+/", (string) $disk->get($path)) ?: []);

        if (count($lines) <= $limit) {
            return;
        }

        $lines = array_slice($lines, -1 * $limit);
        $disk->put($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    private function ensureDirectory(): void
    {
        $disk = $this->disk();

        if (method_exists($disk, 'makeDirectory')) {
            $disk->makeDirectory(self::DIRECTORY);
        }
    }

    private function disk(): FilesystemAdapter
    {
        return $this->filesystem->disk('local');
    }
}
