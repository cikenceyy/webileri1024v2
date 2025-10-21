# Module Proposal & Adlandırma Haritası

## Mevcut Alanlar → Önerilen Modüller
| Mevcut Alan / Namespace | Önerilen Modül İsmi | Konsol / Ekran Etkisi | Notlar |
| --- | --- | --- | --- |
| `App\Modules\Marketing` | **Marketing** | Today Board → Satış boru hattı, müşteri 360 kartı | URI ve rota isimleri `admin/marketing`, `admin.marketing.*` olarak güncellendi; View klasörleri modül kaynakları altında toplanmış durumda.【F:app/Modules/Marketing/Routes/admin.php†L1-L52】【F:app/Modules/Marketing/Resources/views/customers/index.blade.php†L1-L71】 |
| `App\Modules\Inventory` | **Inventory** | P2P Konsolu stok hareketlerini tetikliyor | JS/SCSS girişleri modül bazlı ayrılacak; konsol aksiyonları UseCase katmanına taşınacak. |
| `App\Modules\Procurement` | **Procurement** | P2P Konsolu satınalma siparişi oluşturuyor | `pos` rotaları `purchase-orders` olarak yeniden adlandırılacak; Domain → Application katmanı ile UseCase'ler yazılacak. |
| `App\Modules\Logistics` | **Logistics** | MTO Konsolu sevkiyat iş akışı | Shipment print view'ları module resources/pages/print altına alınacak. |
| `App\Modules\Production` | **Production** | MTO Konsolu iş emirleri | Work Order close işlemi Application UseCase'e taşınacak. |
| `App\Modules\Finance` | **Finance** | P2P Konsolu tahsilat, Today Board KPI | AP/AR ayrımı provider bazlı aktifleştirilecek; soft delete -> status alanı. |
| `App\Modules\Drive` | **Drive** | Tüm modül medya yönetimi | Upload akışları queue=database; Resources dizini `assets/` altına ayrılacak. |
| `App\Modules\Settings` | **Settings** | Şirket ayarları | Domain + Application katmanı ile company/theme yönetimi. |
| `App\Core` + `App\Domains\Core` | **Core** | Tenant kimliği, dashboard, ortak orchestrations | IdentifyTenant middleware, CompanyScope, tasarım token servisleri burada toplanacak. |
| `resources/views/core/consoles/*` | **Consoles** (Today, P2P, MTO, O2C) | Tek ekran iş akışları | Konsol UI'ları `resources/pages/consoles/<slug>.blade.php` altına taşınıp UseCase orchestrator'ları Core'da tanımlanacak. |

## Modül Dizini Şablonu
Her modül aşağıdaki alt klasörleri barındırmalı:
```
app/Modules/<Module>/
  Domain/
    Models/
    Services/
    ValueObjects/
  Application/
    UseCases/
    DTOs/
  Http/
    Controllers/
    Requests/
    Resources/
  Policies/
  Database/
    Migrations/
    Seeders/
    Factories/
  Routes/
    admin.php
  Providers/
    ModuleServiceProvider.php
  Config/
    module.php
  Resources/
    views/
      layouts/
      partials/
      components/
      pages/
    js/
      entry.js
    scss/
      entry.scss
    lang/
      tr/
```
- `Domain` katmanı yalnız iş kurallarını içerir; model event/observer kullanımı Application'a taşınmalı.
- `Application` katmanı orchestrator (UseCase) ve DTO'ları barındıracak; konsollar bu katmana bağlanacak.
- `Resources` altındaki `js` ve `scss` dizinleri Vite tarafından otomatik keşfedilecek.
- Her modül `Providers` içinde ServiceProvider yayınlayacak; provider `bootstrap/app.php` veya yeni Module manifest üzerinden otomatik yüklenecek (Komut 1).

## Eski → Yeni Adlandırma Eşlemesi
| Eski Controller | Yeni Konum | Not |
| --- | --- | --- |
| `App\Modules\Marketing\Http\Controllers\CustomerController` | `App\Modules\Marketing\Http\Controllers\CustomerController` | Namespace güncellendi; rota prefix `marketing`. |
| `App\Modules\Marketing\Http\Controllers\QuoteController` | `App\Modules\Marketing\Http\Controllers\QuoteController` | Print view path modül resources altında tutuluyor. |
| `App\Modules\Marketing\Http\Controllers\OrderController` | `App\Modules\Marketing\Http\Controllers\OrderController` | Finance entegrasyonu UseCase'e taşınacak. |
| `App\Modules\Inventory\Http\Controllers\StockController` | `App\Modules\Inventory\Http\Controllers\StockController` (korunur) | View path `Resources/views/pages/stock/`. |
| `App\Core\Http\Controllers\DashboardController` | `App\Consoles\Today\Http\Controllers\DashboardController` | Consoles katmanına taşınıp UseCase orchestrasyonu Application katmanında yazılacak. |
| `App\Domains\Core\Http\Controllers\Consoles\MtoConsoleController` | `App\Consoles\Mto\Http\Controllers\ConsoleController` | Konsol rotaları `routes/consoles/mto.php` olarak ayrılmalı. |

## Konsol Modülleri
| Konsol | Kullanılan Modüller | Başlangıç UseCase'i | Not |
| --- | --- | --- | --- |
| Today Board | Marketing, Inventory, Finance | `LoadTodayBoardMetrics` | İlk gün teslim edilecek; widget'lar Vite entry ile lazily load edilecek. |
| P2P Console | Procurement, Inventory, Finance | `LaunchP2PFlow` | Purchase order + stok + tahsilat zinciri; queue=database kullanımı. |
| MTO Console | Production, Logistics, Inventory, Marketing | `CreateMakeToOrder` | Order → WorkOrder → Shipment; domain event zinciri planlanacak. |
| O2C Console (plan) | Marketing, Finance | `OrderToCashPipeline` | İleriki komutlarda eklenecek; route skeleton Komut 3'te hazırlanacak. |

## Adım Planı
1. Komut 1: Core provider ve module manifest taslağı (docs referansı olarak bu dosya).<br>
2. Komut 2-3: `resources/` yeniden yapılandırması, Vite entry otomasyonu.<br>
3. Komut 4-6: SoftDeletes kaldırma, `company_id` doğrulama, policy güncellemeleri.<br>
4. Komut 7-10: Konsol orchestrator'ları, Today Board modülerleştirme, Production/Finance entegrasyonu.

