# Runbook — Deploy

## Ön Hazırlık
- `.env` içinde `APP_DEBUG=false`, `APP_ENV=production`, `LOG_LEVEL=warning` olduğundan emin olun.
- Queue worker sayısını kontrol et: prod için 1 (peak saatlerde 2). `php artisan queue:work --queue=default --sleep=3 --tries=3`.
- Scheduler cron satırı tek satır olmalı: `*/10 * * * * php /var/www/artisan schedule:run >> /var/log/cron.log 2>&1`.

## Predeploy
1. Kod güncellemesini çek.
2. `composer install --no-dev --optimize-autoloader`.
3. `npm ci && npm run build`.
4. `php artisan webileri:cloud:predeploy`  
   - `config:cache`
   - `route:cache`
   - `view:cache`
   - `event:cache`
   - PHP-FPM örneklerine `ops/php.ini` ayarlarını dahil ettiğinizden emin olun (opcache validate_timestamps=0 container senaryoları için).
5. `php artisan migrate --force` (gerekliyse).

## Deploy Sonrası
1. Queue worker yeniden başlat: `php artisan queue:restart`.
2. `php artisan webileri:cloud:postdeploy` → cache'in sıcak tutulduğunu doğrular.
3. Healthcheck: `curl -fsS https://app.webileri.example/__healthz` 200 döner.
4. Smoke: `/`, `/login`, `/admin` (auth gerektirebilir).  
   Yetkisiz istek 302/403, yetkili kullanıcı 200 görmeli.
5. Gözlemleyin: queue derinliği (`jobs` tablosu), response süresi, hata logları.

## Bakım Modu
- Kısa bakımda: `php artisan down --render="errors::maintenance"`.
- Çıkışta: `php artisan up`.
- Uzun soluklu işlerde CDN/Load balancer health-check'leri disable edilmeli veya bakıma yönlendirilmeli.

## Geri Dönüş
- Önceki build artefaktına dön.
- `php artisan optimize:clear` gerekirse (cache bozulduysa).
- Queue worker/scheduler reset.
- Healthcheck ve smoke tekrar.
