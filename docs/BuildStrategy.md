# Build Strategy

## Mevcut Durumdan Notlar
- Önceki Vite yapılandırması tek dizi içinde admin/public paketlerini ve UI galeri girişlerini manuel olarak listeliyordu; modül spesifik varlıklar keşfedilmiyor, her şey tek pakete giriyordu.
- @vite çağrıları özellikle `resources/views/layouts/admin.blade.php` ve `resources/views/ui/*.blade.php` dosyalarında dağıtılmış durumda; admin tarafı tek bundle üzerinden ilerliyordu.
- Modül klasörlerinin (örn. `app/Modules/Marketing/Resources/js/marketing.js`) varlığına rağmen herhangi bir otomatik giriş mekanizması olmadığı için kodlar bundle'a dahil edilmiyordu.

## Yeni Çoklu Entry & Otomatik Modül Keşfi
- `vite.config.js` içinde `resources/js/app.js`, `resources/scss/app.scss`, `resources/scss/admin.scss`, `resources/js/admin.js` ve UI galeri girdileri temel paketler olarak tutuldu.
- `moduleEntries()` fonksiyonu `app/Modules/**/Resources/js|scss` yollarını tarayarak her modül için ayrı giriş oluşturuyor. Hedef, modüller arası bağımlılıkları izole ederek küçük parçalar üretmek.
- `fast-glob` bulunuyorsa hızlı keşif için kullanılıyor; erişilemediği ortamlarda `fs.readdirSync` ile aynı desenler manuel olarak yürünerek offline senaryoda da aynı sonuç üretiliyor.

## Dinamik Parça Ayrımı
- `manualChunks` kuralı vendor bağımlılıklarını `vendor`, `vendor-bootstrap`, `vendor-axios` olarak ayırıyor; modül tabanlı dosyalar `mod-<modül>` isimli parçalar halinde derleniyor.
- Normalleştirilmiş yol kontrolü (`id.split(path.sep).join('/')`) Windows ve Unix ortamlarını aynı mantıkla ele alıyor.

## HMR ve Platform Dayanıklılığı
- `server.watch` ayarında polling etkinleştirildi (`usePolling: true`, `interval: 100`) böylece Windows dosya sistemi veya konteyner senaryolarında değişiklikler güvenilir biçimde yakalanıyor.
- Modül keşfi fallback'i sayesinde CI ortamında fast-glob kurulumu gecikse bile `npm run build` ve `npm run dev` komutları aynı çıktıyı üretebiliyor.

## Riskler & Önlemler
- Yeni modül girişleri `data-module` / `data-page` niteliklerine dayanıyor; layout entegrasyonu yapılmayan eski ekranlar için fallback rotası docs/LayoutIntegration.md'de açıklandı.
- `moduleEntries()` tarafından dönen liste Vite plugin input'una enjekte edildi; yanlış isimlendirilmiş dosyalar (ör. `Marketing.Main.js`) otomatik yakalanmayacağı için modül yönergelerinde dosya adlandırma şeması belirlendi (bkz. docs/ModuleAssets.md).
