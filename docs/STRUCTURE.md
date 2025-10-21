# Core / Modules / Consoles Mimari Hedefi

Bu doküman Komut 1 kapsamında eklenen iskeletin nasıl evrileceğini, her katmanın sorumluluklarını ve dizin sözleşmelerini özetler.

## Yüksek Seviye Ayrım
- **Core**: Çok-tenant altyapı, kimlik, rol/izin yönetimi, orchestrations, ortak helper ve enum/VO tanımları.
- **Modules**: İş alanı odaklı paketler. Her modül kendi domain modeli, use-case (Application), HTTP arabirimi, veri tabanı artefaktları ve UI kaynaklarıyla birlikte gelir.
- **Consoles**: KOBİ kullanıcılarının uçtan uca süreçlerini tek ekranda tamamlamasını sağlayan aksiyon konsolları (O2C, P2P, MTO, Today Board vb.). Use-case orchestrations Core katmanından beslenir.

## Core Sözleşmesi
```
app/Core/
├── Access/            # Policy ve izin haritaları (Spatie entegrasyonu)
├── Bus/               # Actions, Events, Listeners, Jobs
├── Orchestrations/    # Modüller arası süreç tanımları
├── Providers/         # Core/Tenancy/Access/Orchestration service provider'ları
├── Support/           # Helpers, console komutları, ortak modeller
└── Tenancy/           # IdentifyTenant middleware, CompanyScope, traits
```
- `CoreServiceProvider` yalnızca core komutlarını ve admin route grubunu bootstrap eder.
- `AccessServiceProvider` Spatie team scope ayarını ve izin kataloğu birleştirmesini yönetir.
- `ModuleLoaderServiceProvider` modül service provider'larını ve view/lang/route kaynaklarını otomatik yükler (eski ModuleManager kaldırıldı).
- `TenancyServiceProvider` middleware alias ve tenant context hazırlığını sürdürür.

## Module Sözleşmesi
Her modül `app/Modules/<Name>/` altında aşağıdaki yapıyı izler:
```
Domain/                # Aggregate root, entity, servis ve kurallar
Application/           # UseCase, Command, Query, DTO
Http/
  ├── Controllers/
  ├── Requests/
  └── Resources/       # API resource / transformer
Database/
  ├── migrations/
  ├── seeders/
  └── factories/
Policies/              # Policy sınıfları
Config/                # Modül konfigürasyonları (permission map vb.)
Routes/                # web.php, admin.php, api.php (opsiyonel)
Providers/             # Modül service provider(ları)
Resources/
  ├── views/
  ├── lang/
  ├── js/
  └── scss/
```
- `ModuleLoaderServiceProvider` dizini tarayarak rotaları (`web`, `admin`, `api`) uygun middleware ile yüklüyor ve view/lang namespace'lerini `Str::kebab($module)` olarak kaydediyor.
- `config/modules.php` alias haritası eski isimleri yeni modül adıyla eşler (örn. `CrmSales` → `Marketing`).
- Komut 3'te JS/SCSS giriş noktaları otomatik keşif ile bundle'a alınacak.

## Consoles Sözleşmesi
```
app/Consoles/
├── Domain/            # Konsol spesifik veri servisleri (örn. Today Board özetleri)
├── Http/Controllers/  # Konsol ekran controller'ları
├── Http/Requests/     # Konsol formları için request objeleri
└── Routes/console.php # /consoles/* rotaları
```
- `ConsoleServiceProvider` dosya mevcutsa `web + tenant + auth + verified` middleware ile `/consoles` rotalarını yüklüyor.
- Görünümler varsayılan olarak `resources/views/consoles/*` altında tutuluyor; gerekirse `app/Consoles/Views` fallback olarak kullanılabilir.

## Örnekler
- `/consoles/today` rotası `TodayBoardController@index` üzerinden çalışır ve `resources/views/consoles/today.blade.php` şablonunu döndürür.
- `/admin/marketing` rotası yeni module loader ile `marketing::demo` görünümünü render eder; bu görünüm `layouts.admin` layout'unu kullanır.

## Sıradaki Adımlar
1. **Komut 2**: UI kaynaklarının modernleştirilmesi tamamlandı; konsol + modül görünümleri yeni layout ve bileşenleri kullanabilir.
2. **Komut 3**: Vite çoklu entry ve modül otomatik entry keşfi; Marketing modülündeki `Resources/js|scss` placeholder'ları burada işlenecek.
3. **Komut 4**: Tenancy scope/trait/middleware taşıma ve `App\Core\Tenancy` namespace'ine kalıcı geçiş.
4. **Komut 5**: `config/permissions.php` sözlüğünün doldurulması ve `AccessServiceProvider` içinde kayıtların yapılması.

Bu doküman Core/Modules/Consoles mimarisine dair tek referans olarak güncellenecek; ilerleyen komutlarda yeni katmanlar veya sözleşme değişiklikleri olduğunda revize edilmelidir.
