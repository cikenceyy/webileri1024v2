# Runbook — Operasyon

## Healthcheck
- Endpoint: `GET /__healthz`
- Yanıt örneği:
```json
{
  "status": "ok",
  "checks": {
    "database": {"status": "ok", "latency_ms": 3},
    "cache": {"status": "ok"},
    "queue": {"status": "ok", "pending": 0}
  },
  "timestamp": "2025-02-20T10:00:00Z"
}
```
- Alarm koşulu: `status != ok` veya `queue.pending > 50`.

## Log Yönetimi
- Kanal: `stack` → `daily` (7 gün saklama)
- Seviye: prod `warning`, staging `notice`, local `debug`.
- Log dosyaları: `storage/logs/laravel-*.log`
- Logrotasyon kontrolü: `LOG_DAILY_DAYS`

## Queue & Worker
- Varsayılan bağlantı: `database`
- Worker başlatma: `php artisan queue:work --queue=default --sleep=3 --tries=3 --backoff=10`
- İzleme: `SELECT COUNT(*) FROM jobs;`
- Alarm: `jobs` > 100 → worker sayısını 2’ye çıkar veya iş yükünü incele.

## Scheduler
- Cron: `*/10 * * * * php /var/www/artisan schedule:run`
- Görevler:
  - `webileri:sequence:audit` (haftalık, Pazartesi 02:30) — sequence tutarlılığı.
  - `webileri:tenancy:audit` (haftalık, Pazartesi 03:30).
  - `queue:prune-batches --hours=48` (günlük 01:15).
  - `model:prune` (günlük 03:00).
  - `queue:retry all` (her 10 dk, arka planda).
- `schedule:work` kullanmayın; cron ile tetikleyin.

## Ölçekleme Eşikleri
- Ortalama yanıt süresi > 400ms ve CPU > %70 (5 dk) → yeni PHP-FPM pod/instance ekle.
- Queue derinliği > 200 (10 dk) → 2. worker aç.
- Database CPU > %80 → sorgu tuning backlog’una bak, read replica değerlendirilir.

## Olay Tepkisi
1. Alarm → `Runbook_Deploy`teki health/smoke adımlarını izleyin.
2. Queue tıkanırsa: `php artisan queue:restart`, problemli job'ları `failed_jobs` tablosundan kontrol edin.
3. Cache bozulursa: `php artisan optimize:clear` ve `webileri:cloud:predeploy`.
4. Uzun süreli hatalarda ops ekibine Slack bildirimi (LOG_SLACK_WEBHOOK_URL ayarlı ise).
