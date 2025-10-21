# Manuel Adımlar ve Doğrulamalar

## Provider Kayıt Noktası
- Laravel 12 uygulaması `bootstrap/app.php` içinde `Application::configure()->withProviders([...])` zincirini kullanıyor.
- Tenancy provider artık CLI sırasında `webileri:tenancy:audit` komutunu kaydediyor ve `tenant` alias'ını yeni middleware sınıfına yönlendiriyor.【F:app/Core/Providers/TenancyServiceProvider.php†L9-L19】
- Eğer proje geçmişte `config/app.php` üzerinden provider yönetiyorsa, merge/rebase sırasında iki listenin senkron olduğundan emin olun.

## Autoload Güncellemesi
- `composer.json` içine `"autoload.files": ["app/Support/helpers.php"]` eklendi.
- Gerekli komut: `composer dump-autoload` (deployment veya lokal geliştirme sırasında çalıştırılmalı).

## Tenancy Audit
- Komut: `php artisan webileri:tenancy:audit`
- Varsayılan log: `storage/logs/tenancy_audit.log` (parametreyle özelleştirilebilir).
- Çalıştırmadan önce tenant tablolarının migrate edildiğinden emin olun; komut `company_id` NULL kayıtlarını ve çapraz tenant ilişkilerini raporlar.【F:app/Core/Tenancy/Console/Commands/TenancyAuditCommand.php†L17-L110】

## Sequence Komutları
- Varsayılan sekansları yerelde kurmak için: `php artisan webileri:sequence:seed` (prod için `--force` gerekli).【F:app/Core/Console/Commands/SequenceSeedCommand.php†L9-L77】
- Sayaç ile gerçek veriler arasındaki farkı görmek için: `php artisan webileri:sequence:audit` → çıktı `docs/Numbering_Audit.md` ve `storage/logs/sequence_audit.log` dosyalarına yazılır.【F:app/Core/Console/Commands/SequenceAuditCommand.php†L9-L108】

## Node Bağımlılıkları
- `fast-glob` devDependency olarak tanımlandı; offline ortamlarda fallback yürütüldüğü için derleme çalışır fakat üretim/CI için `npm install` ile paketin indirilmesi önerilir.
- Registry erişimi kısıtlıysa `moduleEntries()` fonksiyonu otomatik olarak yerel dosya tarayıcısına düşer.

## Test URL'leri
- `/consoles/today` → Yeni Console iskeleti ve layout üzerinden Today Board özetini görüntüler (tenant middleware etkin).【F:app/Consoles/Routes/console.php†L6-L11】【F:resources/views/consoles/today.blade.php†L1-L24】
- `/admin/marketing` → Module loader tarafından servis edilen `marketing::demo` görünümünü doğrular.【F:app/Modules/Marketing/Routes/admin.php†L7-L11】

## Test & QA
- PHP birim/özellik testleri: `php artisan test`
- Tenancy kapsam testleri `tests/Feature/Tenancy/TenancyScopeTest.php` altında yer alıyor ve otomatik şirket atamasını doğruluyor.【F:tests/Feature/Tenancy/TenancyScopeTest.php†L5-L49】

## İleri Komut Notları
- Komut 3: Vite çoklu entry, modül bazlı JS/SCSS keşfi (`app/Modules/*/Resources/js|scss`).
- Komut 4: Tenancy namespace konsolidasyonu (`App\Core\Tenancy`).
- Komut 5: `config/permissions.php` sözlüğü ve `AccessServiceProvider` içindeki politika kayıtları.

## Geri Alma
- Bu komuttaki değişikliklerin tamamı yeni dosya ekleme veya provider listesine satır ekleme şeklinde. Geri almak için ilgili dosyaları silmek ve provider listesinden kaldırmak yeterli.

