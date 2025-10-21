# Layout Integration Checklist

## @vite Girişleri
- Admin şablonu `resources/views/layouts/admin.blade.php` içinde `@vite(['resources/scss/admin.scss', 'resources/js/admin.js'])` çağrısı yeni çoklu entry düzenini kullanır.
- Public/welcome şablonları `@vite(['resources/scss/app.scss', 'resources/js/app.js'])` çağrısına geçirildi; ekstra girişler (UI galeri) belirli sayfalarda kalır.

## Veri Nitelikleri
- `<main class="layout-main">` elementi `data-module`, `data-module-slug` ve `data-page` değerlerini taşır; controller veya Blade seansında `@php($module = 'Marketing')` gibi atamalar yapılmalıdır.
- Eski ekranlar için `@section('module', 'Inventory')` kalıbı desteklenir; layout bu değeri slug'a çevirerek gövde (`<body data-module="inventory">`) üzerinde de saklar.

## Dinamik Modül Yükleyici
- `resources/js/admin.js` `bootModuleAssets()` fonksiyonu ile nitelikleri okuyup ilgili modül paketini `import()` eder.
- Hata durumunda (dosya yok) yakalama bloğu sessizce düşer; kademeli geçişte yeni modül asset'i zorunlu değildir.

## Sayfa Konvansiyonları
- Blade dosyaları `@php($page = 'LeadIndex')` veya `@section('page', 'LeadIndex')` satırıyla sayfa adını yayınlamalı; JS tarafı bu değeri küçük harfe çevirerek kontrol eder.
- Console/today gibi modül dışı ekranlar için `data-module` boş kalabilir, loader hiçbir şey yapmaz.

## Uyum ve İzleme
- Layout gövdesi `data-ui="layout"` ile eski komponentlerin event dinleyicilerini korur; admin-runtime.js bu yapıya dayanarak genel UI davranışlarını çalıştırmaya devam eder.
- Yeni modül eklerken docs/ModuleAssets.md'deki sıralamayı takip edin; aksi halde otomatik keşif dosyayı input listesine alamayabilir.
