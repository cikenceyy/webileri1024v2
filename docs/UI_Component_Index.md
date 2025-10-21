# UI Component Index

| Component | Blade Path |
| --- | --- |
| `<x-ui.alert>` | `resources/views/components/ui/alert.blade.php` |
| `<x-ui.badge>` | `resources/views/components/ui/badge.blade.php` |
| `<x-ui.breadcrumbs>` | `resources/views/components/ui/breadcrumbs.blade.php` |
| `<x-ui.button>` | `resources/views/components/ui/button.blade.php` |
| `<x-ui.card>` | `resources/views/components/ui/card.blade.php` |
| `<x-ui.confirm>` | `resources/views/components/ui/confirm.blade.php` |
| `<x-ui.content>` | `resources/views/components/ui/content.blade.php` |
| `<x-ui.date>` | `resources/views/components/ui/date.blade.php` |
| `<x-ui.drawer>` | `resources/views/components/ui/drawer.blade.php` |
| `<x-ui.empty>` | `resources/views/components/ui/empty.blade.php` |
| `<x-ui.file.icon>` | `resources/views/components/ui/file-icon.blade.php` |
| `<x-ui.inline.edit>` | `resources/views/components/ui/inline-edit.blade.php` |
| `<x-ui.input>` | `resources/views/components/ui/input.blade.php` |
| `<x-ui.kpi>` | `resources/views/components/ui/kpi.blade.php` |
| `<x-ui.modal>` | `resources/views/components/ui/modal.blade.php` |
| `<x-ui.number>` | `resources/views/components/ui/number.blade.php` |
| `<x-ui.page.header>` | `resources/views/components/ui/page-header.blade.php` |
| `<x-ui.pagination>` | `resources/views/components/ui/pagination.blade.php` |
| `<x-ui.row.actions>` | `resources/views/components/ui/row-actions.blade.php` |
| `<x-ui.search>` | `resources/views/components/ui/search.blade.php` |
| `<x-ui.select>` | `resources/views/components/ui/select.blade.php` |
| `<x-ui.skeleton>` | `resources/views/components/ui/skeleton.blade.php` |
| `<x-ui.spinner>` | `resources/views/components/ui/spinner.blade.php` |
| `<x-ui.split.button>` | `resources/views/components/ui/split-button.blade.php` |
| `<x-ui.stat>` | `resources/views/components/ui/stat.blade.php` |
| `<x-ui.switch>` | `resources/views/components/ui/switch.blade.php` |
| `<x-ui.table>` | `resources/views/components/ui/table.blade.php` |
| `<x-ui.tabs>` | `resources/views/components/ui/tabs.blade.php` |
| `<x-ui.textarea>` | `resources/views/components/ui/textarea.blade.php` |
| `<x-ui.toast.stack>` | `resources/views/components/ui/toast-stack.blade.php` |
| `<x-ui.toast>` | `resources/views/components/ui/toast.blade.php` |
| `<x-ui.toolbar>` | `resources/views/components/ui/toolbar.blade.php` |

## CSS & JS Conventions

* `ui-button` varyantları `primary`, `secondary`, `danger`, `ghost`, `outline`, `link` sınıfları üzerinden tanımlandı.
* Legacy `resources/scss/legacy/_admin.scss` dosyası buton varyantlarını ve link/outline davranışlarını kapsayacak şekilde genişletildi.
* Component attribute bağlamında `value` ve doğrulama hataları otomatik olarak form bileşenlerinde (`input`, `select`, `textarea`) çözümlenir.

## Follow-up Backlog

* Console modülleri için ayrı Blade component namespacing planının hayata geçirilmesi.
* Button komponentinde `iconPosition` ve `aria-live` iyileştirmeleri için tasarım gözden geçirmesi.
* `ui-table` yoğunluk tercihlerinin kullanıcı bazlı kalıcı hale getirilmesi (localStorage/cache).
