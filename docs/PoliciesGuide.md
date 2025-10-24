# Policies Guide

## Şirket Bazlı Policy Temelleri
- `App\Core\Access\Policies\CompanyOwnedPolicy` şirket eşleşmesini ilk satırda doğrular ve `$permissionPrefix` ile modül anahtarlarını üretir. Policy sınıfları bu abstract sınıfı extend ederek sadece izin soneklerini (`view`, `update`, `delete`) override etmeden kullanabilir.【F:app/Core/Access/Policies/CompanyOwnedPolicy.php†L5-L49】
- `create` metodu model örneği gerektirmediğinden prefix tanımlıysa doğrudan `<prefix>.create` anahtarını çağırır; prefix boşsa klasik `create` izni aranır. Modül spesifik politikalar bu davranışı `protected $permissionPrefix = 'inventory.product';` gibi tanımlarla özelleştirir.【F:app/Core/Access/Policies/CompanyOwnedPolicy.php†L19-L43】
- Marketing modülü örneği: `CustomerPolicy` ve `OrderPolicy` `CompanyOwnedPolicy`'yi extend ederek `protected $permissionPrefix = 'marketing.customers'` ve `marketing.orders` tanımlar; `import` ve `approve` gibi ekstra eylemler için özel metodlar içerir.【F:app/Modules/Marketing/Policies/CustomerPolicy.php†L1-L20】【F:app/Modules/Marketing/Policies/OrderPolicy.php†L1-L43】

## Provider Entegrasyonu
- `AccessServiceProvider` AuthServiceProvider’ı extend ederek policy haritasını genişletmeye hazır hale getirir; tenant context ve izin kataloğu aynı provider içinde yönetilir.【F:app/Core/Providers/AccessServiceProvider.php†L5-L95】
- Policy eşlemelerini eklemek için `$policies` dizisine sınıfları ekleyip `php artisan make:policy` çıktısını `app/Modules/<Modul>/Policies` altına koyun. Provider bu eşlemeleri otomatik kaydeder.

## Blade & Controller Kullanımı
- Blade tarafında `@can('marketing.lead.update')` çağrıları tenant scope’da çalışır; `biz` rolü Gate::before sayesinde true döner, diğer roller policy/pivot kontrolüne takılır.【F:app/Core/Providers/AccessServiceProvider.php†L64-L68】
- Controller seviyesinde `$this->authorize('update', $lead);` ifadesi `CompanyOwnedPolicy` kontrolünden geçerek hem şirket eşleşmesini hem de izin anahtarını doğrular.

## Geliştirme İpuçları
1. Yeni modül izinlerini önce `config/permissions.php` veya modül içi `Config/permissions.php` dosyasına ekleyin; provider değişiklikleri CLI sırasında cache’ten düşürür.【F:app/Core/Providers/AccessServiceProvider.php†L52-L63】
2. Feature testleri yazarken `PermissionRegistrar::setPermissionsTeamId($companyId)` çağırıp kullanıcıya rol ataması yapmayı unutmayın; örnek için `tests/Feature/Access/TenantPermissionTest.php` dosyasına bakabilirsiniz.【F:tests/Feature/Access/TenantPermissionTest.php†L9-L69】
3. Geçiş döneminde legacy policy’ler varsa `CompanyOwnedPolicy` içindeki `permissionKey` metodunu override ederek eski anahtarları alias’layabilirsiniz.
