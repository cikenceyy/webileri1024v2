# K2 — Route & Module Relocation Guide

## Pricelist flow (Inventory → Marketing)
- Routes relocated under `/admin/marketing/pricelists` with names `admin.marketing.pricelists.index` and `admin.marketing.pricelists.show`.
- Controller namespace now `App\Modules\Marketing\Http\Controllers\Admin\PricelistController`.
- Views live at `marketing::admin.pricelists.*` and share module assets from `app/Modules/Marketing/Resources/{js,scss}/pricelists`.
- Permissions renamed to `marketing.pricelist.{view,create,update,delete}` and bound in `MarketingServiceProvider`.
- Sidebar entry appears under **CRM & Pazarlama → Fiyat Listeleri**.

## BOM flow (Inventory → Production)
- Routes relocated under `/admin/production/boms` with names `admin.production.boms.*`.
- Controller namespace now `App\Modules\Production\Http\Controllers\Admin\BomController` with policy guard `App\Modules\Production\Policies\BomPolicy`.
- BOM views and assets moved to `production::admin.boms.*` with Vite sources in `app/Modules/Production/Resources`.
- Permissions renamed to `production.boms.{view,create,update,delete}` and registered in `ProductionServiceProvider`.
- Sidebar entry appears under **Üretim → Ürün Reçeteleri**.

## Finance navigation tightening
- Sidebar for **Finans & Muhasebe** now links to invoices and only exposes:
  - `Faturalar` (`admin.finance.invoices.index`)
  - `Tahsilatlar` (`admin.finance.receipts.index`)
  - `Cashbook (Lite)` (`admin.finance.cashbook.index`)
- Heavy console routes (`collections`) are wrapped by `config('features.finance.collections_console')` (default `false`).
- Buttons and links to Tahsilat Konsolu render only when the feature flag is enabled.

## Legacy redirects & feature flags
- Transitional redirects for `/admin/inventory/pricelists*` → `/admin/marketing/pricelists*` and `/admin/inventory/bom*` → `/admin/production/boms*` are enabled while `config('features.legacy_routing.inventory_{pricelists|bom}')` remain `true`.
- Toggle heavy finance menus and consoles via `config/features.php`:
  - `finance.collections_console`
  - `finance.reports_center`
  - `finance.aging`
  - `finance.ap_review`
  - `finance.payment_suggestions`

## Settings integration hint
- Sales Order create/edit forms now read defaults from `SettingsReader` for payment terms, price list and tax inclusivity. Forms expose `data-default-price-list` and `data-default-tax-inclusive` attributes for future automation.
