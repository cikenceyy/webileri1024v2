# Ortam Yapılandırma Matrisi

| Ayar | Local | Staging | Production |
| --- | --- | --- | --- |
| APP_ENV | local | staging | production |
| APP_DEBUG | true | false | false |
| APP_URL | http://localhost | https://staging.webileri.example | https://app.webileri.example |
| LOG_LEVEL | debug | notice | warning |
| LOG_STACK | single | daily | daily |
| LOG_DAILY_DAYS | 14 | 7 | 7 |
| CACHE_DRIVER / CACHE_STORE | database | database | file (tek node) / database (çok node) |
| SESSION_DRIVER | database | cookie | cookie |
| QUEUE_CONNECTION | database | database | database |
| QUEUE_WORKERS | horizon kapalı, `queue:work --once` | 1 worker | 1 (peak saatlerde 2) |
| BROADCAST_DRIVER | log | log | log |
| MAIL_MAILER | log | smtp (sandbox) | smtp |
| VITE_USE_DYNAMIC_IMPORT | true | true | true |
| PERMISSION_TEAMS | true | true | true |
| TENANT_RESOLVE_STRATEGY | host (fallback `.env`) | host | host |
| CRON_BASE_MINUTES | 10 | 10 | 10 |
| LOG_CHANNEL | stack | stack | stack |
| HEALTH_ENDPOINT | `/__healthz` (auth yok) | `/__healthz` | `/__healthz` |
