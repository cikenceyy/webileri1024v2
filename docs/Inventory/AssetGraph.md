# Asset Graph & Vite Analizi

## Vite Yapılandırması
| Öğe | Değer | Öneri |
| --- | --- | --- |
| Vite girişleri | `resources/scss/app.scss`, `resources/js/app.js`, `resources/scss/admin.scss`, `resources/js/admin.js`, modül JS/SCSS glob (`app/Modules/**/Resources/js/*.js`, `app/Modules/**/Resources/scss/*.scss`) | Çoklu entry + modül auto-discovery aktif; yeni modül eklerken `Resources/js/<module>.js` oluşturmak yeterli.【F:vite.config.js†L5-L67】 |
| Laravel Vite plugin | `refresh: true` | Varsayılan ayar korunuyor; modül view güncellemelerinde HMR tetikleniyor. Gerekirse `refresh: ['resources/views/**', 'app/Modules/**/Resources/views/**']` olarak genişletilebilir. |
| Manual chunks | `vendor`, `vendor-bootstrap`, `vendor-axios`, `mod-<module>` | Dinamik `manualChunks` modül bazında küçük paketler üretir; Marketing → `mod-marketing`, Inventory → `mod-inventory`. |

## JavaScript Grafiği
- `resources/js/admin.js` layout entry'si; `data-module` / `data-page` attribute'larını okuyup ilgili modül paketini dinamik olarak `import()` eder.【F:resources/js/admin.js†L1-L34】
- `app/Modules/*/Resources/js/<module>.js` dosyaları yalnız çağrıldıkları anda yüklenir; örn. Marketing modülü `marketing.js` içinde SCSS'ini import eder ve sayfa bazlı boot fonksiyonu yayınlar.【F:app/Modules/Marketing/Resources/js/marketing.js†L1-L156】
- `resources/js/pages/ui-gallery.js` yalnız tasarım galerisi sayfasında kullanılmalı; şimdilik manuel entry ile ayrılmış.
- Ağır bağımlılıklar: Bootstrap (JS) + Popper; axios (devDependency) yüklü fakat kullanılmıyor; tree-shaking ile `bootstrap/js/dist/*` modülleri granular import edilmiş.

**Öneriler:**
1. `data-module` tabanlı dinamik import devrede; yeni modül eklerken `<main data-module="<Name>">` meta'sının set edildiğinden emin ol.
2. Konsol ekranları için modül chunk'larını `data-page` ile genişleterek Today Board gibi ekranlara özel JS dosyaları (örn. `resources/js/consoles/today-board.js`) bağlanabilir.
3. UI Galerisi script'i (ve SCSS) `resources/pages/ui` dizinine taşınarak yeni page bazlı entry konsepti gösterilebilir.

## SCSS Hiyerarşisi
| Dosya | İçerik | Bootstrap Import Sırası | Risk | Öneri |
| --- | --- | --- | --- | --- |
| `resources/scss/app.scss` | Design tokens (`tokens/*`), Bootstrap temel dosyaları, module SCSS | `functions` → `variables` → `bootstrap` (tam) | `to-rgb()` ile `var()` birlikte kullanıldığında tonlama hatası oluşabilir; token'lar literal renklerle override edilmiş. | Token değişkenlerini `:root` tanımlarında literal hex ile sürdürmeye devam edin; CSS `var()` tanımları Bootstrap importundan sonra `:root` bloğuna taşınmalı. |
| `resources/scss/pages/ui-gallery.scss` | UI showcase'e özgü stiller | `@use` yapılmamış; `@import` ile base'e bağlı | Tekil entry olmasına rağmen global değişkenlere erişiyor | Page-specific SCSS'i `@use '../app' as *;` ile modüler yapın; galeri stilleri `resources/pages/ui` altına taşınmalı. |
| `resources/scss/components/*` | Form, table, card vb. | Bootstrap sonrası override | Token referansları `var(--ui-*)` kullanıyor | Component SCSS'i modüler hale getirirken `@forward` ile tree-shake edilebilir. |

## Asset Riskleri
| ID | Başlık | Şiddet | Açıklama | Dosya | Öneri |
| --- | --- | --- | --- | --- | --- |
| A-01 | Bootstrap + CSS var() çakışması | P2 | `resources/scss/app.scss` içinde token değişkenleri `var(--ui-color-*)` ile doğrudan komponentlere aktarılıyor; Bootstrap `to-rgb()` hesaplarında literal renk bekliyor. | `resources/scss/app.scss` | Token override'larını literal değerlerle yap, CSS değişkenlerini `:root` bloklarında Bootstrap sonrasında tanımla. |
| A-02 | Modül chunk'larının eksik entry riski | P2 | `resources/js/admin.js` dinamik import ile yalnız ilgili modül paketini çağırır; ancak `app/Modules/<Modul>/Resources/js/<modul>.js` dosyası eksikse konsol ekranı boş kalabilir. | `resources/js/admin.js` | Yeni modül eklerken JS boot dosyası + SCSS import'u oluştur; legacy sayfalar için `data-module` metadata'sını set et. |
| A-03 | Axios kullanılmıyor | P3 | `package.json` devDependency olarak axios içeriyor ancak app.js içinde çağrı yok; bundle analizinde gereksiz bağımlılık olabilir. | `package.json` | Kullanılmıyorsa kaldır, aksi halde API client modülüne taşı. |

## Otomasyon Fırsatları
- Vite config'ine `glob.sync('resources/modules/**/entry.js')` tabanlı giriş eklenip her modül için otomatik JS/SCSS derlemesi sağlanabilir.
- Laravel Mix'ten Vite'e geçişten kalan `resources/css` klasörü boş; kaldırılarak tek kaynak `scss` olarak tanımlanabilir.
- Manifest tabanlı preload: Konsol layout'unda `@vite` yerine `Vite::useBuildDirectory('modules/<module>')` pattern'i için hazırlık yapılmalı.

