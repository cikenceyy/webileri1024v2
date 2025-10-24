# K8 — HR & ACL Özeti

Bu sürümle birlikte temel insan kaynakları yönetimi modülü ve rol/izin matrisi kurgusu devreye alındı.

## Modül Başlıkları

- **Personel Ayarları:** Departman, Ünvan ve Çalışma Tipi sözlükleri için CRUD ekranları.
- **Personel Dizini:** Arama ve çoklu filtre desteğiyle çalışan listesi.
- **Personel Kartı:** İletişim, organizasyon bilgileri, işe giriş/çıkış tarihleri ve not alanı. Kullanıcı eşlemesi isteğe bağlıdır.
- **HR Navigasyonu:** Admin menüsüne “İK Yönetimi” başlığı altında Personel Dizini ve Personel Ayarları eklenmiştir.

## Rol Tanımları

| Rol           | Açıklama |
|---------------|----------|
| `super_admin` | Platform genelinde tam yetki (tüm izinler). |
| `owner`       | Tenant yöneticisi; platform dışı süper aksiyonlar hariç tüm modüllerde tam yetki. |
| `accountant`  | Finans, İK, Envanter, Lojistik ve Ayarlar modüllerinde tam yetki; pazarlama müşterilerine erişemez, siparişlerde yalnızca görüntüleme/düzenleme/confirm/iptal yetkisi bulunur. |
| `operator`    | Günlük operasyon odağı: Envanter, Lojistik ve Work Order ekranlarında oluştur/güncelle yetkisi; finans ekranları yalnızca okunur. |
| `intern`      | Sistemde yalnızca görüntüleme (view/index/show) izinleri; müşteri bilgileri maskelenir, işlem yapamaz. |

İzin setleri `config/permissions.php` dosyasındaki `roles` anahtarında merkezi olarak tanımlanır. Wildcard (`*`) ve `@view` makrolarıyla tüm izinler veya yalnız görüntüleme izinleri devreye alınır.

## Görünürlük / Maskeleme

- `accountant` ve `intern` rollerinde pazarlama müşteri kayıtlarının e-posta, telefon ve adres alanları API çıktılarında boş döner (`CustomerResource`).
- CustomerPolicy, bu roller için müşteri kayıtlarına erişimi tamamen engeller.
- Sipariş ekranları müşteri adını göstermekle birlikte hassas alanları render etmez; JSON tüketicileri `CustomerResource` üzerinden maskeleme alır.

## CLI Kullanımı

Yeni rol atama komutu:

```bash
php artisan acl:assign {company_id} {user_id} {role}
```

Komut yalnızca platform operasyon ekibince kullanılır; verilen şirket-id ve kullanıcı-id doğrulandıktan sonra ilgili rol Spatie Permission takımı bağlamında atanır.

## Politika Güncellemeleri

- Pazarlama müşteri ve sipariş politikaları muhasebeci/stajyer rollerine özel guardrail ekler.
- HR modülü için Department, Title, EmploymentType ve Employee politikaları `hr.*` izinlerini temel alır.

## Navigasyon & UI

- HR modülü admin menüsüne eklenmiştir.
- Personel ayarları ekranları basit kart formları, personel dizini ise tablo + filtre yapısıyla gelir.
- Arşivleme işlemi, personeli pasif yaparak kapanış tarihini otomatik doldurur.

## İlgili Belgeler

- Rol matrisi ve izin anahtarları: `config/permissions.php`
- ACL seed akışı: `database/seeders/RolesAndPermissionsSeeder.php`
- HR ekranları: `app/Modules/HR/...`
- CLI komutu: `php artisan acl:assign`
