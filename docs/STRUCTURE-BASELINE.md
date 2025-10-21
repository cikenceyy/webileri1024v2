# Repository Structure Baseline (Komut 4 Güncellemesi)

## Ağaç Görünümü (derinlik ≤ 3)
```
./
├── app/
│   ├── Consoles/
│   │   ├── Domain/
│   │   ├── Http/
│   │   └── Routes/
│   ├── Core/
│   │   ├── Access/
│   │   ├── Auth/
│   │   ├── Bus/
│   │   ├── Orchestrations/
│   │   ├── Providers/
│   │   └── Tenancy/
│   ├── Domains/
│   ├── Http/
│   ├── Models/
│   ├── Modules/
│   │   ├── Drive/
│   │   ├── Finance/
│   │   ├── Inventory/
│   │   ├── Logistics/
│   │   ├── Marketing/
│   │   ├── Procurement/
│   │   ├── Production/
│   │   └── Settings/
│   ├── Providers/
│   └── Support/
├── bootstrap/
│   └── cache/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docs/
│   ├── Inventory/
│   ├── STRUCTURE.md (hedef mimari)
│   ├── STRUCTURE-BASELINE.md
│   └── UI_* rehberleri
├── public/
├── resources/
│   ├── js/
│   │   ├── components/
│   │   └── pages/
│   ├── scss/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── tokens/
│   │   └── vendors/
│   └── views/
│       ├── components/
│       ├── consoles/
│       ├── layouts/
│       ├── legacy/
│       ├── pages/
│       └── partials/
├── routes/
│   ├── admin.php
│   ├── console.php
│   └── web.php
└── vite.config.js
```
> Hariç: `vendor/`, `node_modules/`, `public/build/`.

## Laravel ve Provider Gözlemleri
- `bootstrap/app.php` Laravel 12 yapılandırmasını kullanıyor ve provider kaydı `->withProviders([...])` üzerinden yapılıyor.【F:bootstrap/app.php†L1-L33】
- Çekirdek provider listesinde şimdi `TenancyServiceProvider` doğrudan `App\Core\Tenancy\Middleware\IdentifyTenant` sınıfını aliaslıyor ve CLI çalışırken `webileri:tenancy:audit` komutunu kayıt ediyor.【F:app/Core/Providers/TenancyServiceProvider.php†L9-L19】

## Rota Kümeleri
- `routes/web.php`: genel landing.
- `routes/admin.php`: `['tenant','auth','verified']` middleware zinciriyle admin modülleri.
- Konsol rotaları `app/Consoles/Routes/console.php` altında `['web','auth','tenant']` grubu ile servis ediliyor; Today Board yeni UI bileşenlerini kullanıyor.【F:app/Consoles/Routes/console.php†L6-L11】【F:resources/views/consoles/today.blade.php†L1-L24】
- Modül rotaları `app/Modules/*/Routes` içinde ve tenant middleware’i taşıyor; Marketing demo route module loader’ın çalıştığını doğruluyor.【F:app/Modules/Marketing/Routes/admin.php†L7-L11】

## Tenancy Güncellemeleri
- `App\Core\Tenancy\Middleware\IdentifyTenant` artık asıl domain çözümlemesini içeriyor; eski `App\Core\Http\Middleware\IdentifyTenant` dosyası yalnızca geçiş amaçlı bir alias.【F:app/Core/Tenancy/Middleware/IdentifyTenant.php†L5-L87】【F:app/Core/Http/Middleware/IdentifyTenant.php†L5-L10】
- Global scope ve trait implementasyonları `App\Core\Tenancy\Scopes\CompanyScope` ve `App\Core\Tenancy\Traits\BelongsToCompany` altında konsolide edildi; eski namespace dosyaları geriye dönük uyum için alias olarak kalıyor.【F:app/Core/Tenancy/Scopes/CompanyScope.php†L5-L32】【F:app/Core/Tenancy/Traits/BelongsToCompany.php†L5-L28】【F:app/Core/Scopes/CompanyScope.php†L5-L10】【F:app/Core/Traits/BelongsToCompany.php†L5-L11】
- Tenancy audit komutu tüm `company_id` kolonlarını tarayarak `NULL` kayıtları ve çapraz tenant ilişkilerini raporluyor; log `storage/logs/tenancy_audit.log` altında tutuluyor.【F:app/Core/Tenancy/Console/Commands/TenancyAuditCommand.php†L17-L110】
- Factories artık şirket id’sini otomatik üretmek için `Company::factory()` kullanıyor; `Company` modeli yeni factory sınıfına yönlendirme içeriyor.【F:database/factories/UserFactory.php†L5-L33】【F:database/factories/Core/CompanyFactory.php†L5-L24】【F:app/Core/Models/Company.php†L5-L28】

## Modül ve Kaynak Durumu
- Mevcut modüller Domain/Http/Routes/Resources klasörlerini koruyor; JS/SCSS entry’leri Komut 3 ile otomatik yükleme için hazır.
- Marketing modülü artık CRM/Sales domainini barındırıyor; tüm controller/view/policy dosyaları `App\Modules\Marketing` altına taşındı ve layout data attribute’ları view composer üzerinden otomatik set ediliyor.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L105】

## Risk & Notlar
- Tenancy middleware’i devre dışı bırakıp doğrudan modül controller’larına erişen legacy route’lar audit komutu tarafından tespit edilecek `NULL company_id` satırlarına sebep olabilir.
- Heuristik ilişki kontrolü composite FK’lar uygulanana kadar erken uyarı üretir; Komut 6’da fiziksel constraint’ler planlanmalı.
- Seeder’lar yalnızca local ortamda çok tenant’lı demo veri oluşturuyor; production’da çalışmıyor.【F:database/seeders/DatabaseSeeder.php†L9-L29】

