# Inventory Dosya Eşleme Tablosu

| Eski Yol | Yeni Yol |
| --- | --- |
| `resources/views/inventory/home.blade.php` | `app/Modules/Inventory/Resources/views/home.blade.php` |
| `resources/scss/inventory/home.scss` | `app/Modules/Inventory/Resources/scss/home.scss` |
| `resources/js/inventory/home.js` | `app/Modules/Inventory/Resources/js/home.js` |
| `resources/views/inventory/stock/*.blade.php` | `app/Modules/Inventory/Resources/views/stock/console.blade.php` |
| `resources/scss/inventory/stock_console.scss` | `app/Modules/Inventory/Resources/scss/stock_console.scss` |
| `resources/js/inventory/stock_console.js` | `app/Modules/Inventory/Resources/js/stock_console.js` |
| `resources/views/inventory/products/*.blade.php` | `app/Modules/Inventory/Resources/views/products/{index,show,components}.blade.php` |
| `resources/js/inventory/products_*.js` | `app/Modules/Inventory/Resources/js/products_{index,show}.js` |
| `resources/scss/inventory/products_*.scss` | `app/Modules/Inventory/Resources/scss/products_{index,show}.scss` |
| `resources/views/inventory/warehouses/*.blade.php` | `app/Modules/Inventory/Resources/views/warehouses/{index,show}.blade.php` |
| `resources/views/inventory/pricelists/*.blade.php` | `app/Modules/Inventory/Resources/views/pricelists/{index,show}.blade.php` |
| `resources/views/inventory/bom/*.blade.php` | `app/Modules/Inventory/Resources/views/bom/{index,show}.blade.php` |
| `resources/views/inventory/settings/index.blade.php` | `app/Modules/Inventory/Resources/views/settings/index.blade.php` |

Tüm ilişkili JS/SCSS dosyaları aynı isimlendirme ile `app/Modules/Inventory/Resources/{js,scss}` altında tutulmaktadır.
