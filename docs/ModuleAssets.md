# Module Assets Playbook

## Dosya Adlandırma Kuralları
- Her modül için temel JS giriş dosyası `app/Modules/<Modul>/Resources/js/<modul>.js` şeklinde, küçük harfli slug ile tanımlanır (örn. `Marketing` → `marketing.js`).
- Stiller aynı slug ile `Resources/scss/<modul>.scss` dosyasında tutulur ve JS girişinde `import '../scss/<modul>.scss'` ile zincire eklenir.
- Ek sayfa bazlı init ihtiyacı olduğunda `export default function(context)` imzası üzerinden `context.page` parametresi kullanılmalıdır.

## Dinamik Yükleme Akışı
1. `resources/views/layouts/admin.blade.php` ana `<main>` alanına `data-module`, `data-module-slug` ve `data-page` niteliklerini bırakır.
2. `resources/js/admin.js` içindeki `bootModuleAssets()` bu nitelikleri okuyarak ilgili modül slug'ını belirler ve Vite alias'ı `@modules` ile doğru dosyayı `import()` eder.
3. Modül JS'i `default` fonksiyon döndürür; layout tarafından sağlanan `page` parametresi ile sayfa özel davranışlar aktive edilir.
4. Modül JS, ihtiyaç duyduğu SCSS'yi kendisi içeri aldığı için aynı anda CSS chunk'ı da yüklenir.

## Geri Uyumluluk
- Layout ana gövde (`<body>`) hala `data-module="<slug>"` niteliklerini taşıyor; eski `resources/js/modules/*.js` dosyaları mevcut davranışlarını sürdürebiliyor.
- Modül varlıkları tanımlı değilse `import()` hatası yutuluyor, böylece kademeli geçiş sırasında boş modüller problem yaratmıyor.

## Test Alanları
- Marketing modülü örneği `marketing::demo` görünümü içinde `@php($module = 'Marketing')` ve `@php($page = 'Demo')` ile nitelikleri set ediyor.
- JS tarafı hero bloğunu `data-marketing-hero` üzerinden işaretleyip `marketing-hero--active` sınıfını ekliyor; stil `app/Modules/Marketing/Resources/scss/marketing.scss` içinde.

## İzlenecek Adımlar
- Yeni modül eklendiğinde `Resources/js` ve `Resources/scss` dosyaları açıldıktan sonra ek konfigürasyona gerek yoktur; Vite otomatik olarak input listesine dahil eder.
- Sayfa isimleri PascalCase/TitleCase tutulabilir; JS içinde `context.page.toLowerCase()` ile normalize edilerek kontrol sağlanıyor.
