# Local-Only Seeding Strategy

## Amaç
Production verisini etkilemeden demo rollerini ve örnek şirketi yalnızca yerel geliştirici ortamlarında oluşturmak.

## Guard Mekanizması
- `DatabaseSeeder` ilk olarak `RolesAndPermissionsSeeder`'ı çağırır; seeder çalışması `app()->environment('local')` veya `app.url` içinde `localhost` geçmesine bağlıdır.【F:database/seeders/DatabaseSeeder.php†L9-L18】【F:database/seeders/RolesAndPermissionsSeeder.php†L25-L44】
- Spatie Permission kurulu değilse seeder erken çıkış yapar; böylece bağımlılık yüklenmediğinde hata oluşmaz.【F:database/seeders/RolesAndPermissionsSeeder.php†L17-L22】

## Seed Akışı
1. Mevcut bir şirket yoksa `Company::factory()` ile demo şirket oluşturulur ve kimliği PermissionRegistrar’a set edilir.【F:database/seeders/RolesAndPermissionsSeeder.php†L46-L69】
2. Merkezi izin kataloğu (`config/permissions.php` + modül izinleri) birleştirilir ve eksik izinler `Permission::findOrCreate` ile eklenir.【F:database/seeders/RolesAndPermissionsSeeder.php†L71-L108】
3. Roller (`biz`, `patron`, `muhasebeci`, `stajyer`) oluşturulup izin matrisine göre `syncPermissions` çağrılır.【F:database/seeders/RolesAndPermissionsSeeder.php†L109-L137】
4. Seeder sonunda PermissionRegistrar cache’i temizleyerek CLI sonrası güncel hakların kullanılmasını sağlar.【F:database/seeders/RolesAndPermissionsSeeder.php†L139-L141】

## Notlar
- Demo kullanıcı ataması yapılmaz; geliştiriciler test senaryolarında `User::factory()` ile kullanıcı oluşturup `assignRole()` çağırmalıdır.
- Production’da rolleri dağıtmak gerektiğinde seeder guard koşulu güncellenebilir veya özel bir komut hazırlanabilir.
