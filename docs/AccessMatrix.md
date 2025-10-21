# Access Matrix

## Mevcut Durum
- Spatie Permission v6.21 konfigürasyonu teams modunu etkinleştirip `company_id` alanını takım anahtarı olarak kullanıyor.【F:config/permission.php†L3-L47】
- AccessServiceProvider tenant çözümünü kullanarak PermissionRegistrar team id’sini set ediyor, merkezi `config/permissions.php` ile modül bazlı izin dosyalarını birleştiriyor ve CLI çalıştırmalarında cache’i temizliyor.【F:app/Core/Providers/AccessServiceProvider.php†L5-L95】
- Marketing modülü kendi izinlerini `app/Modules/Marketing/Config/permissions.php` altında yayınlıyor; diğer modüller ekledikçe otomatik keşfe dahil olacak.【F:app/Modules/Marketing/Config/permissions.php†L1-L7】
- `RolesAndPermissionsSeeder` yalnızca local/localhost ortamında tetikleniyor; demo şirket oluşturup izinleri seed ederek rollerin şirket bazlı pivotlarına yazıyor.【F:database/seeders/RolesAndPermissionsSeeder.php†L1-L141】

## Rol → İzin Matris Özeti
| Rol | Kapsam | İzin Seti |
| --- | --- | --- |
| **biz** | Süper admin | Tüm izinler; Gate::before kancası sayesinde `user->can(*)` her zaman `true`.【F:app/Core/Providers/AccessServiceProvider.php†L36-L63】【F:database/seeders/RolesAndPermissionsSeeder.php†L111-L137】 |
| **patron** | Yönetici | Tüm modüller için tam CRUD; super-admin olmadan modül bazlı kontrollere tabi. Roller arası geçişte `PermissionRegistrar` team id ayarı gereklidir.【F:database/seeders/RolesAndPermissionsSeeder.php†L111-L137】 |
| **muhasebeci** | Finans odaklı | `finance.*` izinleri + tüm modüllerde `.view/.index/.show` hakları; finansal yazma dışında diğer modüller salt-okunur.【F:database/seeders/RolesAndPermissionsSeeder.php†L118-L137】 |
| **stajyer** | Salt-okunur | Yalnızca `.view/.index/.show` izinleri; yazma operasyonları reddedilir. Feature testiyle doğrulanır.【F:database/seeders/RolesAndPermissionsSeeder.php†L127-L137】【F:tests/Feature/Access/TenantPermissionTest.php†L31-L65】 |

## Yetkilendirme Akışı
1. `tenant()` helper middleware tarafından şirketi container’a bağlar; AccessServiceProvider bu değeri PermissionRegistrar’a işler.【F:app/Core/Tenancy/Middleware/IdentifyTenant.php†L16-L59】【F:app/Core/Providers/AccessServiceProvider.php†L36-L63】
2. Route/Controller tarafı `@can` veya policy çağırdığında Gate önce `biz` rolünü kontrol eder; değilse şirket bazlı izin tablosu değerlendirilir.【F:app/Core/Providers/AccessServiceProvider.php†L64-L68】
3. Policy genişletmeleri `CompanyOwnedPolicy` üzerinden `company_id` eşleşmesi yaptıktan sonra izin anahtarını doğrular.【F:app/Core/Access/Policies/CompanyOwnedPolicy.php†L5-L49】
4. Test senaryoları tenant ID bağlamının doğru yazıldığı ve rol/pivot izolasyonunun çalıştığını doğrular.【F:tests/Feature/Access/TenantPermissionTest.php†L7-L69】

## Yapılacaklar
- Modüller taşındıkça kendi `Config/permissions.php` dosyaları eklenmeli ve AccessServiceProvider otomatik olarak kataloglamaya devam edecektir.
- Policy haritası gerçek modül modellerine (`Inventory\ProductPolicy`, `Finance\InvoicePolicy` vb.) genişletilecek.
- Salt-okunur roller için UI bileşenlerinde `@cannot` kullanımı yaygınlaştırılacak.
