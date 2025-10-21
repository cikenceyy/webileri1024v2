# Cloud Cost & Hardening Checklist

## Mevcut Durum

| Alan | Bulgular | Kaynak |
| --- | --- | --- |
| Queue | Varsayılan sürücü `database`; ek sürücüler yapılandırılmış fakat worker stratejisi dokümante edilmemiş. Retry süreleri bağlantı env değişkenlerine dayanıyor. | `config/queue.php` |
| Scheduler | `routes/console.php` yalnızca `inspire` komutunu barındırıyor; planlı işler merkezi değil. | `routes/console.php` |
| Cache | Varsayılan mağaza `database`; `CACHE_STORE` env değişkeni ile seçiliyor. Route/config cache kullanımına dair komut yok. | `config/cache.php` |
| Assets | Çoklu entry + modül keşfi için Vite yapılandırması mevcut; bundle ayrıştırma `manualChunks` ile yapılıyor. | `vite.config.js` |
| Logs | Varsayılan `stack` kanalı `single` dosyasını kullanıyor; seviye `LOG_LEVEL=debug`. Günlük rotasyonu yapılandırılmamış. | `config/logging.php`, `.env.example` |
| Debug | Telescope/Debugbar gibi paketler kurulu değil. | composer.json |
| Env | `.env.example` yerel odaklı; prod/stage için önerilen bayraklar bulunmuyor. | `.env.example` |
| Healthcheck | Sadece Laravel varsayılan `/up` uç noktası tanımlı, sistem bileşeni denetimleri yok. | `bootstrap/app.php` |
| Queue/Scheduler Dokümantasyonu | Queue worker sayısı, cron sıklığı veya maliyet stratejisine dair runbook yok. | doküman yok |
| DB Gözlemi | Çok sayıda tenant tablosu `company_id` + statü alanlarına göre sorgulanıyor; composite index planı yazılı değil. | `app/Modules/**/Domain/Models` |

## Uygulanan Aksiyonlar

- `.env.example` prod/staging profilleri ile güncellendi; log seviyesi `warning`, cache/session cookie/file olarak ayarlandı.
- `webileri:cloud:predeploy` ve `webileri:cloud:postdeploy` artisan komutları cache yönetimini otomatikleştiriyor.
- `HttpCacheHeaders` middleware’i ve `http.cache` alias’ı statik sayfalar için 304/ETag desteği sağlıyor.
- Scheduler görevleri `routes/console.php` altında toplanıp queue prune, audit ve retry akışları planlandı.
- `/__healthz` rotası DB, cache ve queue sağlık kontrollerini JSON olarak sunuyor.
- `config/logging.php` prod odaklı günlük rotasyon ve `warning` seviyesiyle sadeleştirildi.
- `Runbook_Deploy.md`, `Runbook_Operate.md`, `EnvMatrix.md` ve `DB_Tuning_Backlog.md` operasyon rehberi olarak eklendi.
- `ops/php.ini` OPCache ve realpath önerilerini içeriyor; CloudCostChecklist bu aksiyonların izini tutuyor.
