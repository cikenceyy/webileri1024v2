# Route Inventory

Bu doküman mevcut Laravel rotalarını derler ve Core / Modules / Consoles ayrımına geçişte kullanılacak önerileri içerir.

## Özet
- Laravel 12.x projesi; toplam rota grupları `web`, `admin`, modül bazlı `admin/<module>` segmentleri ve Artisan konsol komutu içeriyor.
- Modül dosyalarındaki tüm rotalar `tenant`, `auth`, `verified` orta katmanlarıyla korunuyor; login/logout gibi çekirdek akışlar `routes/admin.php` içinde.
- CRM/Sales isimleri hâlen `marketing` olarak geçiyor; Marketing modülüne geçiş sırasında URI/name standardizasyonu önerilir.

## Genel Notlar
- Tablo sütunları: **Method**, **URI**, **Name**, **Middleware**, **Controller**, **Tahmini Modül**, **Öneri**.
- Resource rotalarında HTTP metodları birleştirilmiş şekilde listelenmiştir.

### Public / Web Rotası
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | / | web.welcome (varsayılan) | web | `WelcomeController::__invoke` | Landing | Çoklu tenant yönlendirmesi Today Board açılışıyla hizalanmalı.

### Admin Çekirdek (routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/login | admin.auth.login.show | tenant, guest (grup) | `LoginController@showLoginForm` | Core/Auth | Login ekranı ileride Consoles → Access Console altında gruplanabilir.
| POST | /admin/login | admin.auth.login.attempt | tenant, guest, throttle:10,1 | `LoginController@login` | Core/Auth | Throttle değeri tenant bazlı konfigürasyona taşınmalı.
| GET | /admin | admin.dashboard | tenant, auth, verified | `DashboardController::__invoke` | Core/Dashboard | Konsol odaklı Today Board açılışına yönlendirilmesi planlanmalı.
| GET | /admin/today-board | admin.today-board | tenant, auth, verified | `TodayBoardController::__invoke` | Consoles/Today | İlk geçişte Consoles katmanına taşınacak referans ekran.
| GET | /admin/consoles/mto | admin.consoles.mto | tenant, auth, verified | `MtoConsoleController@index` | Consoles/MTO | Konsol dizininde Vite entry ayrıştırması önerilir.
| POST | /admin/consoles/mto/orders/{order}/work-orders | admin.consoles.mto.work-orders.store | tenant, auth, verified | `MtoConsoleController@storeWorkOrder` | Consoles/MTO | WorkOrder domain modülüne messaging ile bağlanmalı.
| POST | /admin/consoles/mto/shipments/{shipment}/ship | admin.consoles.mto.shipments.ship | tenant, auth, verified | `MtoConsoleController@ship` | Consoles/MTO | Shipment flow Logistics modül servislerine devredilmeli.
| GET | /admin/consoles/p2p | admin.consoles.p2p | tenant, auth, verified | `P2pConsoleController@index` | Consoles/P2P | Konsol layout'u Today Board ile uyumlanmalı.
| POST | /admin/consoles/p2p/invoices/{invoice}/collect | admin.consoles.p2p.invoices.collect | tenant, auth, verified | `P2pConsoleController@collect` | Consoles/P2P | Finance modül ödeme akışıyla orchestrate edilecek.
| POST | /admin/consoles/p2p/stock-items/{stockItem}/purchase-orders | admin.consoles.p2p.stock-items.purchase-orders | tenant, auth, verified | `P2pConsoleController@createPurchaseOrder` | Consoles/P2P | Procurement modül use-case'leri tetiklenmeli.
| GET | /admin/ui | admin.ui.index | tenant, auth, verified | `UIController@index` | Core/UI Library | Tasarım sistemi Vite çoklu entry ile ayrıştırılmalı.
| POST | /admin/logout | admin.auth.logout | tenant, auth, verified | `LoginController@logout` | Core/Auth | Konsol üst menüsüne taşınırken CSRF koruması doğrulanmalı.

### Konsol Komutları (routes/console.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| CLI | inspire | N/A | console | Closure (Inspiring::quote) | Core/DevOps | Konsol komutları module provider’lara bölünebilir.

### Inventory Modülü (app/Modules/Inventory/Routes/admin.php)
> **K2 Notu:** Fiyat listesi rotaları bu adımdan itibaren Marketing modülünde `admin.marketing.pricelists.*` adıyla yayımlanmaktadır.
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/inventory/categories | admin.inventory.categories.index | tenant, auth, verified, web | `CategoryController@index` | Inventory | Category listing Today Board filtrelerine bağlanmalı.
| GET | /admin/inventory/categories/create | admin.inventory.categories.create | tenant, auth, verified, web | `CategoryController@create` | Inventory | Form view modül içi layout'a taşınmalı.
| POST | /admin/inventory/categories | admin.inventory.categories.store | tenant, auth, verified, web | `CategoryController@store` | Inventory | Request validation DTO'ya alınmalı.
| GET | /admin/inventory/categories/{category} | admin.inventory.categories.show | tenant, auth, verified, web | `CategoryController@show` | Inventory | Route model binding slug'laştırılabilir.
| GET | /admin/inventory/categories/{category}/edit | admin.inventory.categories.edit | tenant, auth, verified, web | `CategoryController@edit` | Inventory | Form state UI tokens ile güncellenmeli.
| PUT/PATCH | /admin/inventory/categories/{category} | admin.inventory.categories.update | tenant, auth, verified, web | `CategoryController@update` | Inventory | Soft delete kaldırıldığında audit trail eklenmeli.
| DELETE | /admin/inventory/categories/{category} | admin.inventory.categories.destroy | tenant, auth, verified, web | `CategoryController@destroy` | Inventory | Hard delete öncesi stok ilişkileri kontrol edilmeli.
| GET | /admin/inventory/units | admin.inventory.units.index | tenant, auth, verified, web | `UnitController@index` | Inventory | Unit listesi token'lı formlara taşınacak.
| GET | /admin/inventory/units/create | admin.inventory.units.create | tenant, auth, verified, web | `UnitController@create` | Inventory | Modal form varyantı hazırlanmalı.
| POST | /admin/inventory/units | admin.inventory.units.store | tenant, auth, verified, web | `UnitController@store` | Inventory | Base unit sync bellek optimizasyonu gerektiriyor.
| GET | /admin/inventory/warehouses | admin.inventory.warehouses.index | tenant, auth, verified, web | `WarehouseController@index` | Inventory | Warehouse haritası layout/partials ayrımı bekliyor.
| GET | /admin/inventory/warehouses/create | admin.inventory.warehouses.create | tenant, auth, verified, web | `WarehouseController@create` | Inventory | Form Vite entry'sine ayrılmalı.
| POST | /admin/inventory/warehouses | admin.inventory.warehouses.store | tenant, auth, verified, web | `WarehouseController@store` | Inventory | Coordinates için token tanımı yapılmalı.
| GET | /admin/inventory/warehouses/{warehouse} | admin.inventory.warehouses.show | tenant, auth, verified, web | `WarehouseController@show` | Inventory | Map bileşeni module resources/js altına taşınacak.
| GET | /admin/inventory/warehouses/{warehouse}/edit | admin.inventory.warehouses.edit | tenant, auth, verified, web | `WarehouseController@edit` | Inventory | Layout grid UI tokens ile güncellenecek.
| PUT/PATCH | /admin/inventory/warehouses/{warehouse} | admin.inventory.warehouses.update | tenant, auth, verified, web | `WarehouseController@update` | Inventory | Soft delete kalkınca closure raporlanmalı.
| DELETE | /admin/inventory/warehouses/{warehouse} | admin.inventory.warehouses.destroy | tenant, auth, verified, web | `WarehouseController@destroy` | Inventory | Hard delete risk kaydına eklendi.
| GET | /admin/inventory/products | admin.inventory.products.index | tenant, auth, verified, web | `ProductController@index` | Inventory | Liste component'i module scoped `ui-table` varyantına dönüştürülecek.
| GET | /admin/inventory/products/create | admin.inventory.products.create | tenant, auth, verified, web | `ProductController@create` | Inventory | Çoklu media uploader Drive modülüyle entegre edilmeli.
| POST | /admin/inventory/products | admin.inventory.products.store | tenant, auth, verified, web | `ProductController@store` | Inventory | Request DTO + pipeline planlanıyor.
| GET | /admin/inventory/products/{product} | admin.inventory.products.show | tenant, auth, verified, web | `ProductController@show` | Inventory | Show view module resources/pages dizinine taşınmalı.
| GET | /admin/inventory/products/{product}/edit | admin.inventory.products.edit | tenant, auth, verified, web | `ProductController@edit` | Inventory | Form layout tokens'e uydurulmalı.
| PUT/PATCH | /admin/inventory/products/{product} | admin.inventory.products.update | tenant, auth, verified, web | `ProductController@update` | Inventory | Variant sync job planlanmalı.
| DELETE | /admin/inventory/products/{product} | admin.inventory.products.destroy | tenant, auth, verified, web | `ProductController@destroy` | Inventory | Soft delete kalktığında cascade stratejisi belirlenmeli.
| POST | /admin/inventory/products/{product}/gallery | admin.inventory.products.gallery.add | tenant, auth, verified, web | `ProductController@addGallery` | Inventory | Drive belge doğrulaması zorunlu.
| DELETE | /admin/inventory/products/{product}/gallery/{gallery} | admin.inventory.products.gallery.remove | tenant, auth, verified, web | `ProductController@removeGallery` | Inventory | Media referansı Drive provider'a aktarılmalı.
| GET | /admin/inventory/products/{product}/variants | admin.inventory.products.variants.index | tenant, auth, verified, web | `VariantController@index` | Inventory | Variant view module layout'unu kullanmalı.
| GET | /admin/inventory/products/{product}/variants/create | admin.inventory.products.variants.create | tenant, auth, verified, web | `VariantController@create` | Inventory | Modal form planlanıyor.
| POST | /admin/inventory/products/{product}/variants | admin.inventory.products.variants.store | tenant, auth, verified, web | `VariantController@store` | Inventory | SKU unique kontrolü Domain servisinde yapılmalı.
| GET | /admin/inventory/variants/{variant} | admin.inventory.variants.show (shallow) | tenant, auth, verified, web | `VariantController@show` | Inventory | Shallow rota slug bazlı yapılabilir.
| GET | /admin/inventory/variants/{variant}/edit | admin.inventory.variants.edit | tenant, auth, verified, web | `VariantController@edit` | Inventory | Form layout unify edilecek.
| PUT/PATCH | /admin/inventory/variants/{variant} | admin.inventory.variants.update | tenant, auth, verified, web | `VariantController@update` | Inventory | Soft delete -> arşiv planı yapılmalı.
| DELETE | /admin/inventory/variants/{variant} | admin.inventory.variants.destroy | tenant, auth, verified, web | `VariantController@destroy` | Inventory | Hard delete risk analizi.
| GET | /admin/inventory/import/products/sample | admin.inventory.import.products.sample | tenant, auth, verified, web | `ImportController@sample` | Inventory | Storage erişim izinleri gözden geçirilmeli.
| GET | /admin/inventory/import/products | admin.inventory.import.products.form | tenant, auth, verified, web | `ImportController@form` | Inventory | Upload component module resources/js altına taşınacak.
| POST | /admin/inventory/import/products | admin.inventory.import.products.store | tenant, auth, verified, web | `ImportController@store` | Inventory | Queue=database iş akışına alınmalı.
| GET | /admin/inventory/stock | admin.inventory.stock.index | tenant, auth, verified, web | `StockController@index` | Inventory | Rapor layout tokens'e göre güncellenecek.
| GET | /admin/inventory/stock/in | admin.inventory.stock.in.form | tenant, auth, verified, web | `StockController@inForm` | Inventory | Form modül resources/pages/stock altına taşınmalı.
| POST | /admin/inventory/stock/in | admin.inventory.stock.in.store | tenant, auth, verified, web | `StockController@storeIn` | Inventory | Transaction isolation kontrol edilmeli.
| GET | /admin/inventory/stock/out | admin.inventory.stock.out.form | tenant, auth, verified, web | `StockController@outForm` | Inventory | UI component reusable yapılacak.
| POST | /admin/inventory/stock/out | admin.inventory.stock.out.store | tenant, auth, verified, web | `StockController@storeOut` | Inventory | Audit log eklenecek.
| GET | /admin/inventory/stock/transfer | admin.inventory.stock.transfer.form | tenant, auth, verified, web | `StockController@transferForm` | Inventory | Inter-warehouse validation Domain servislerine taşınacak.
| POST | /admin/inventory/stock/transfer | admin.inventory.stock.transfer.store | tenant, auth, verified, web | `StockController@storeTransfer` | Inventory | Event sourcing planlanmalı.
| GET | /admin/inventory/stock/adjust | admin.inventory.stock.adjust.form | tenant, auth, verified, web | `StockController@adjustForm` | Inventory | Layout tokens standardize edilecek.
| POST | /admin/inventory/stock/adjust | admin.inventory.stock.adjust.store | tenant, auth, verified, web | `StockController@storeAdjust` | Inventory | Approval workflow modül planı.
| GET | /admin/inventory/reports/onhand | admin.inventory.reports.onhand | tenant, auth, verified, web | `ReportController@onHand` | Inventory | Rapor sayfaları module resources/pages'e taşınacak.
| GET | /admin/inventory/reports/ledger | admin.inventory.reports.ledger | tenant, auth, verified, web | `ReportController@ledger` | Inventory | Ledger UI table state modül tokens ile hizalanacak.
| GET | /admin/inventory/reports/valuation | admin.inventory.reports.valuation | tenant, auth, verified, web | `ReportController@valuation` | Inventory | Değerleme raporu Finance ile entegre edilecek.

### Marketing (app/Modules/Marketing/Routes/admin.php)
> Rotası `admin/marketing` ve `admin.marketing.*` prefix'leriyle modül loader üzerinden aktif durumda.

| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/marketing/customers | admin.marketing.customers.index | tenant, auth, verified, web | `CustomerController@index` | Marketing | URI prefix `marketing` olarak yeniden adlandırılmalı.
| GET | /admin/marketing/customers/create | admin.marketing.customers.create | tenant, auth, verified, web | `CustomerController@create` | Marketing | Form bileşenleri module resources/components'e taşınacak.
| POST | /admin/marketing/customers | admin.marketing.customers.store | tenant, auth, verified, web | `CustomerController@store` | Marketing | Request -> UseCase dönüşümü planlanıyor.
| GET | /admin/marketing/customers/{customer} | admin.marketing.customers.show | tenant, auth, verified, web | `CustomerController@show` | Marketing | Show view module layout'a taşınacak.
| GET | /admin/marketing/customers/{customer}/edit | admin.marketing.customers.edit | tenant, auth, verified, web | `CustomerController@edit` | Marketing | Inline edit planı.
| PUT/PATCH | /admin/marketing/customers/{customer} | admin.marketing.customers.update | tenant, auth, verified, web | `CustomerController@update` | Marketing | Soft delete kaldırıldığında state machine uyarlanmalı.
| DELETE | /admin/marketing/customers/{customer} | admin.marketing.customers.destroy | tenant, auth, verified, web | `CustomerController@destroy` | Marketing | Hard delete risk; audit log.
| GET | /admin/marketing/customers/{customer}/show | admin.marketing.customers.show (explicit) | tenant, auth, verified, web | `CustomerController@show` | Marketing | Duble rota slug standardizasyonu ile sadeleşmeli.
| GET | /admin/marketing/customers/{customer}/contacts | admin.marketing.contacts.index | tenant, auth, verified, web | `ContactController@index` | Marketing | Nested resource Vite entry'ye ayrılmalı.
| POST | /admin/marketing/customers/{customer}/contacts | admin.marketing.contacts.store | tenant, auth, verified, web | `ContactController@store` | Marketing | Inline creation modülü.
| PUT/PATCH | /admin/marketing/contacts/{contact} | admin.marketing.contacts.update | tenant, auth, verified, web | `ContactController@update` | Marketing | API-first pattern'e taşınmalı.
| DELETE | /admin/marketing/contacts/{contact} | admin.marketing.contacts.destroy | tenant, auth, verified, web | `ContactController@destroy` | Marketing | Hard delete risk.
| GET | /admin/marketing/customers/{customer}/addresses | admin.marketing.addresses.index | tenant, auth, verified, web | `AddressController@index` | Marketing | Address component UI tokens.
| POST | /admin/marketing/customers/{customer}/addresses | admin.marketing.addresses.store | tenant, auth, verified, web | `AddressController@store` | Marketing | Lokasyon modülü planı.
| PUT/PATCH | /admin/marketing/addresses/{address} | admin.marketing.addresses.update | tenant, auth, verified, web | `AddressController@update` | Marketing | Soft delete -> arşiv planı.
| DELETE | /admin/marketing/addresses/{address} | admin.marketing.addresses.destroy | tenant, auth, verified, web | `AddressController@destroy` | Marketing | Hard delete risk.
| GET | /admin/marketing/opportunities | admin.marketing.opportunities.index | tenant, auth, verified, web | `OpportunityController@index` | Marketing | Pipeline board Today Board ile hizalanacak.
| GET | /admin/marketing/opportunities/create | admin.marketing.opportunities.create | tenant, auth, verified, web | `OpportunityController@create` | Marketing | Layout modül resources/pages.
| POST | /admin/marketing/opportunities | admin.marketing.opportunities.store | tenant, auth, verified, web | `OpportunityController@store` | Marketing | Stage transitions Domain servislerine taşınacak.
| GET | /admin/marketing/opportunities/{opportunity} | admin.marketing.opportunities.show | tenant, auth, verified, web | `OpportunityController@show` | Marketing | Kanban entegre.
| GET | /admin/marketing/opportunities/{opportunity}/edit | admin.marketing.opportunities.edit | tenant, auth, verified, web | `OpportunityController@edit` | Marketing | Soft delete removal planı.
| PUT/PATCH | /admin/marketing/opportunities/{opportunity} | admin.marketing.opportunities.update | tenant, auth, verified, web | `OpportunityController@update` | Marketing | Stage change audit.
| DELETE | /admin/marketing/opportunities/{opportunity} | admin.marketing.opportunities.destroy | tenant, auth, verified, web | `OpportunityController@destroy` | Marketing | Hard delete risk.
| GET | /admin/marketing/quotes | admin.marketing.quotes.index | tenant, auth, verified, web | `QuoteController@index` | Marketing | Quote view module layout'a taşınacak.
| GET | /admin/marketing/quotes/create | admin.marketing.quotes.create | tenant, auth, verified, web | `QuoteController@create` | Marketing | Form Vite entry'sine ayrılacak.
| POST | /admin/marketing/quotes | admin.marketing.quotes.store | tenant, auth, verified, web | `QuoteController@store` | Marketing | Document numbering Core modüle taşınmalı.
| GET | /admin/marketing/quotes/{quote} | admin.marketing.quotes.show | tenant, auth, verified, web | `QuoteController@show` | Marketing | Layout unify.
| GET | /admin/marketing/quotes/{quote}/edit | admin.marketing.quotes.edit | tenant, auth, verified, web | `QuoteController@edit` | Marketing | Soft delete removal planı.
| PUT/PATCH | /admin/marketing/quotes/{quote} | admin.marketing.quotes.update | tenant, auth, verified, web | `QuoteController@update` | Marketing | UseCase orchestrasyonu.
| DELETE | /admin/marketing/quotes/{quote} | admin.marketing.quotes.destroy | tenant, auth, verified, web | `QuoteController@destroy` | Marketing | Hard delete risk.
| GET | /admin/marketing/quotes/{quote}/print | admin.marketing.quotes.print | tenant, auth, verified, web | `QuoteController@print` | Marketing | Print view module resources/views/quotes altına taşınacak.
| GET | /admin/marketing/orders | admin.marketing.orders.index | tenant, auth, verified, web | `OrderController@index` | Marketing | Sales Order -> Finance entegrasyonu.
| GET | /admin/marketing/orders/create | admin.marketing.orders.create | tenant, auth, verified, web | `OrderController@create` | Marketing | Layout unify.
| POST | /admin/marketing/orders | admin.marketing.orders.store | tenant, auth, verified, web | `OrderController@store` | Marketing | Domain event Finance modülüne.
| GET | /admin/marketing/orders/{order} | admin.marketing.orders.show | tenant, auth, verified, web | `OrderController@show` | Marketing | Layout unify.
| GET | /admin/marketing/orders/{order}/edit | admin.marketing.orders.edit | tenant, auth, verified, web | `OrderController@edit` | Marketing | Soft delete removal.
| PUT/PATCH | /admin/marketing/orders/{order} | admin.marketing.orders.update | tenant, auth, verified, web | `OrderController@update` | Marketing | UseCase orchestrasyonu.
| DELETE | /admin/marketing/orders/{order} | admin.marketing.orders.destroy | tenant, auth, verified, web | `OrderController@destroy` | Marketing | Hard delete risk.
| GET | /admin/marketing/orders/{order}/print | admin.marketing.orders.print | tenant, auth, verified, web | `OrderController@print` | Marketing | Print layout tokens.
| GET | /admin/marketing/reports/sales | admin.marketing.reports.sales | tenant, auth, verified, web | `ReportController@sales` | Marketing | Rapor UI standardizasyonu.
| GET | /admin/marketing/reports/sales/print | admin.marketing.reports.sales.print | tenant, auth, verified, web | `ReportController@salesPrint` | Marketing | Print layout unify.
| GET | /admin/marketing/reports/sales/export | admin.marketing.reports.sales.export | tenant, auth, verified, web | `ReportController@salesExport` | Marketing | Export job queue=database.
| GET | /admin/marketing/activities | admin.marketing.activities.index | tenant, auth, verified, web | `ActivityController@index` | Marketing | Timeline component module resources.
| POST | /admin/marketing/activities | admin.marketing.activities.store | tenant, auth, verified, web | `ActivityController@store` | Marketing | Activity bus → real-time planı.
| PUT/PATCH | /admin/marketing/activities/{activity} | admin.marketing.activities.update | tenant, auth, verified, web | `ActivityController@update` | Marketing | Soft delete removal planı.
| DELETE | /admin/marketing/activities/{activity} | admin.marketing.activities.destroy | tenant, auth, verified, web | `ActivityController@destroy` | Marketing | Hard delete risk.
| POST | /admin/marketing/notes | admin.marketing.notes.store | tenant, auth, verified, web | `NoteController@store` | Marketing | Note UI tokens.
| DELETE | /admin/marketing/notes/{note} | admin.marketing.notes.destroy | tenant, auth, verified, web | `NoteController@destroy` | Marketing | Hard delete risk.
| POST | /admin/marketing/attachments | admin.marketing.attachments.store | tenant, auth, verified, web | `AttachmentController@store` | Marketing | Drive modülü entegrasyonu.
| DELETE | /admin/marketing/attachments/{attachment} | admin.marketing.attachments.destroy | tenant, auth, verified, web | `AttachmentController@destroy` | Marketing | Hard delete risk.
| GET | /admin/marketing/import/customers | admin.marketing.customers.import.form | tenant, auth, verified, web | `ImportController@form` | Marketing | Import UI module resources/pages.
| POST | /admin/marketing/import/customers | admin.marketing.customers.import | tenant, auth, verified, web | `ImportController@importCustomers` | Marketing | Queue=database planı.

### Logistics (app/Modules/Logistics/Routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/logistics/shipments | admin.logistics.shipments.index | tenant, auth, verified, web | `ShipmentController@index` | Logistics | UI layout module resources/pages.
| GET | /admin/logistics/shipments/create | admin.logistics.shipments.create | tenant, auth, verified, web | `ShipmentController@create` | Logistics | Form tokens standardizasyonu.
| POST | /admin/logistics/shipments | admin.logistics.shipments.store | tenant, auth, verified, web | `ShipmentController@store` | Logistics | Event to Today Board.
| GET | /admin/logistics/shipments/{shipment} | admin.logistics.shipments.show | tenant, auth, verified, web | `ShipmentController@show` | Logistics | Layout unify.
| GET | /admin/logistics/shipments/{shipment}/edit | admin.logistics.shipments.edit | tenant, auth, verified, web | `ShipmentController@edit` | Logistics | Inline actions planı.
| PUT/PATCH | /admin/logistics/shipments/{shipment} | admin.logistics.shipments.update | tenant, auth, verified, web | `ShipmentController@update` | Logistics | Soft delete removal.
| DELETE | /admin/logistics/shipments/{shipment} | admin.logistics.shipments.destroy | tenant, auth, verified, web | `ShipmentController@destroy` | Logistics | Hard delete risk.
| GET | /admin/logistics/shipments/{shipment}/print | admin.logistics.shipments.print | tenant, auth, verified, web | `ShipmentController@print` | Logistics | Print layout module resources.
| GET | /admin/logistics/reports/register | admin.logistics.reports.register | tenant, auth, verified, web | `ReportController@register` | Logistics | Report UI tokens.

### Finance (app/Modules/Finance/Routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/finance/invoices | admin.finance.invoices.index | tenant, auth, verified, web | `InvoiceController@index` | Finance | Layout unify, module entry.
| GET | /admin/finance/invoices/create | admin.finance.invoices.create | tenant, auth, verified, web | `InvoiceController@create` | Finance | Form tokens.
| POST | /admin/finance/invoices | admin.finance.invoices.store | tenant, auth, verified, web | `InvoiceController@store` | Finance | UseCase orchestrasyonu.
| GET | /admin/finance/invoices/{invoice} | admin.finance.invoices.show | tenant, auth, verified, web | `InvoiceController@show` | Finance | Layout unify.
| GET | /admin/finance/invoices/{invoice}/edit | admin.finance.invoices.edit | tenant, auth, verified, web | `InvoiceController@edit` | Finance | Soft delete removal.
| PUT/PATCH | /admin/finance/invoices/{invoice} | admin.finance.invoices.update | tenant, auth, verified, web | `InvoiceController@update` | Finance | Payment integration.
| DELETE | /admin/finance/invoices/{invoice} | admin.finance.invoices.destroy | tenant, auth, verified, web | `InvoiceController@destroy` | Finance | Hard delete risk.
| GET | /admin/finance/invoices/{invoice}/print | admin.finance.invoices.print | tenant, auth, verified, web | `InvoiceController@print` | Finance | Print layout unify.
| GET | /admin/finance/invoices/from-order/{order} | admin.finance.invoices.from-order | tenant, auth, verified, web | `InvoiceController@createFromOrder` | Finance | Sales Order → Invoice flow unify.
| GET | /admin/finance/receipts | admin.finance.receipts.index | tenant, auth, verified, web | `ReceiptController@index` | Finance | Layout unify.
| GET | /admin/finance/receipts/create | admin.finance.receipts.create | tenant, auth, verified, web | `ReceiptController@create` | Finance | Form tokens.
| POST | /admin/finance/receipts | admin.finance.receipts.store | tenant, auth, verified, web | `ReceiptController@store` | Finance | Soft delete removal.
| GET | /admin/finance/receipts/{receipt} | admin.finance.receipts.show | tenant, auth, verified, web | `ReceiptController@show` | Finance | Layout unify.
| GET | /admin/finance/receipts/{receipt}/edit | admin.finance.receipts.edit | tenant, auth, verified, web | `ReceiptController@edit` | Finance | Soft delete removal.
| PUT/PATCH | /admin/finance/receipts/{receipt} | admin.finance.receipts.update | tenant, auth, verified, web | `ReceiptController@update` | Finance | UseCase orchestrasyonu.
| DELETE | /admin/finance/receipts/{receipt} | admin.finance.receipts.destroy | tenant, auth, verified, web | `ReceiptController@destroy` | Finance | Hard delete risk.
| GET | /admin/finance/allocations | admin.finance.allocations.index | tenant, auth, verified, web | `AllocationController@index` | Finance | UI tokens.
| POST | /admin/finance/allocations | admin.finance.allocations.store | tenant, auth, verified, web | `AllocationController@store` | Finance | Domain event planı.
| DELETE | /admin/finance/allocations/{allocation} | admin.finance.allocations.destroy | tenant, auth, verified, web | `AllocationController@destroy` | Finance | Hard delete risk.
| GET | /admin/finance/bank-accounts | admin.finance.bank-accounts.index | tenant, auth, verified, web | `BankAccountController@index` | Finance | Layout unify.
| GET | /admin/finance/bank-accounts/create | admin.finance.bank-accounts.create | tenant, auth, verified, web | `BankAccountController@create` | Finance | Form tokens.
| POST | /admin/finance/bank-accounts | admin.finance.bank-accounts.store | tenant, auth, verified, web | `BankAccountController@store` | Finance | Soft delete removal.
| GET | /admin/finance/bank-accounts/{bank_account}/edit | admin.finance.bank-accounts.edit | tenant, auth, verified, web | `BankAccountController@edit` | Finance | Layout unify.
| PUT/PATCH | /admin/finance/bank-accounts/{bank_account} | admin.finance.bank-accounts.update | tenant, auth, verified, web | `BankAccountController@update` | Finance | UseCase orchestrasyonu.
| DELETE | /admin/finance/bank-accounts/{bank_account} | admin.finance.bank-accounts.destroy | tenant, auth, verified, web | `BankAccountController@destroy` | Finance | Hard delete risk.
| GET | /admin/finance/bank-transactions | admin.finance.bank-transactions.index | tenant, auth, verified, web | `BankTransactionController@index` | Finance | UI tokens.
| POST | /admin/finance/bank-transactions | admin.finance.bank-transactions.store | tenant, auth, verified, web | `BankTransactionController@store` | Finance | Domain event.
| DELETE | /admin/finance/bank-transactions/{bankTransaction} | admin.finance.bank-transactions.destroy | tenant, auth, verified, web | `BankTransactionController@destroy` | Finance | Hard delete risk.
| GET | /admin/finance/reports/aging | admin.finance.reports.aging | tenant, auth, verified, web | `ReportController@aging` | Finance | Report UI tokens.
| GET | /admin/finance/reports/receipts | admin.finance.reports.receipts | tenant, auth, verified, web | `ReportController@receipts` | Finance | Report UI tokens.
| GET | /admin/finance/reports/summary | admin.finance.reports.summary | tenant, auth, verified, web | `ReportController@summary` | Finance | Report UI tokens.
| GET | /admin/finance/reports/aging-print | admin.finance.reports.aging-print | tenant, auth, verified, web | `ReportController@aging` (print view) | Finance | Print layout unify.
| GET | /admin/finance/reports/receipts-print | admin.finance.reports.receipts-print | tenant, auth, verified, web | `ReportController@receipts` (print) | Finance | Print layout unify.
| GET | /admin/finance/reports/summary-print | admin.finance.reports.summary-print | tenant, auth, verified, web | `ReportController@summary` (print) | Finance | Print layout unify.
| GET | /admin/finance/ap-invoices | admin.finance.ap-invoices.index | tenant, auth, verified, web | `ApInvoiceController@index` | Finance | Layout unify.
| GET | /admin/finance/ap-invoices/{ap_invoice} | admin.finance.ap-invoices.show | tenant, auth, verified, web | `ApInvoiceController@show` | Finance | Layout unify.
| PUT/PATCH | /admin/finance/ap-invoices/{ap_invoice} | admin.finance.ap-invoices.update | tenant, auth, verified, web | `ApInvoiceController@update` | Finance | UseCase orchestrasyonu.
| GET | /admin/finance/ap-payments | admin.finance.ap-payments.index | tenant, auth, verified, web | `ApPaymentController@index` | Finance | Layout unify.
| GET | /admin/finance/ap-payments/create | admin.finance.ap-payments.create | tenant, auth, verified, web | `ApPaymentController@create` | Finance | Form tokens.
| POST | /admin/finance/ap-payments | admin.finance.ap-payments.store | tenant, auth, verified, web | `ApPaymentController@store` | Finance | Domain event.

### Procurement (app/Modules/Procurement/Routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/procurement/pos | admin.procurement.pos.index | tenant, auth, verified, web | `PoController@index` | Procurement | URI `purchase-orders` olarak sadeleştirilmeli.
| GET | /admin/procurement/pos/create | admin.procurement.pos.create | tenant, auth, verified, web | `PoController@create` | Procurement | Form tokens.
| POST | /admin/procurement/pos | admin.procurement.pos.store | tenant, auth, verified, web | `PoController@store` | Procurement | Domain event.
| GET | /admin/procurement/pos/{po} | admin.procurement.pos.show | tenant, auth, verified, web | `PoController@show` | Procurement | Layout unify.
| PUT/PATCH | /admin/procurement/pos/{po} | admin.procurement.pos.update | tenant, auth, verified, web | `PoController@update` | Procurement | UseCase orchestrasyonu.
| GET | /admin/procurement/grns | admin.procurement.grns.index | tenant, auth, verified, web | `GrnController@index` | Procurement | Layout unify.
| GET | /admin/procurement/grns/create | admin.procurement.grns.create | tenant, auth, verified, web | `GrnController@create` | Procurement | Form tokens.
| POST | /admin/procurement/grns | admin.procurement.grns.store | tenant, auth, verified, web | `GrnController@store` | Procurement | Domain event.
| GET | /admin/procurement/grns/{grn} | admin.procurement.grns.show | tenant, auth, verified, web | `GrnController@show` | Procurement | Layout unify.

### Production (app/Modules/Production/Routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/production/work-orders | admin.production.work-orders.index | tenant, auth, verified, web | `WorkOrderController@index` | Production | Layout unify.
| POST | /admin/production/work-orders | admin.production.work-orders.store | tenant, auth, verified, web | `WorkOrderController@store` | Production | Domain orchestrasyonu.
| GET | /admin/production/work-orders/{workOrder} | admin.production.work-orders.show | tenant, auth, verified, web | `WorkOrderController@show` | Production | Layout unify.
| PUT/PATCH | /admin/production/work-orders/{workOrder} | admin.production.work-orders.update | tenant, auth, verified, web | `WorkOrderController@update` | Production | UseCase orchestrasyonu.
| PATCH | /admin/production/work-orders/{workOrder}/close | admin.production.work-orders.close | tenant, auth, verified, web | `WorkOrderController@close` | Production | Close işlemi domain event'e taşınacak.

### Drive (app/Modules/Drive/Routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/drive | admin.drive.media.index | tenant, auth, verified, web | `MediaController@index` | Drive | Layout unify.
| POST | /admin/drive/upload | admin.drive.media.store | tenant, auth, verified, web | `MediaController@store` | Drive | Chunk upload planı.
| POST | /admin/drive/upload-many | admin.drive.media.store_many | tenant, auth, verified, web | `MediaController@storeMany` | Drive | Queue=database.
| POST | /admin/drive/{media}/replace | admin.drive.media.replace | tenant, auth, verified, web | `MediaController@replace` | Drive | Versioning planı.
| GET | /admin/drive/{media}/download | admin.drive.media.download | tenant, auth, verified, web | `MediaController@download` | Drive | Signed URL planı.
| DELETE | /admin/drive/{media} | admin.drive.media.destroy | tenant, auth, verified, web | `MediaController@destroy` | Drive | Hard delete risk.
| POST | /admin/drive/{media}/toggle-important | admin.drive.media.toggle_important | tenant, auth, verified, web | `MediaController@toggleImportant` | Drive | Flag UI tokens.

### Settings (app/Modules/Settings/Routes/admin.php)
| Method | URI | Name | Middleware | Controller | Tahmini Modül | Öneri |
| --- | --- | --- | --- | --- | --- | --- |
| GET | /admin/settings/company | admin.settings.company.edit | tenant, auth, verified, web | `CompanyController@edit` | Settings | UI tokens.
| PUT | /admin/settings/company | admin.settings.company.update | tenant, auth, verified, web | `CompanyController@update` | Settings | Theme tokens Core design system ile hizalanacak.
| POST | /admin/settings/company/domains | admin.settings.company.domains.store | tenant, auth, verified, web | `CompanyDomainController@store` | Settings | Domain verification planı.
| POST | /admin/settings/company/domains/{domain}/make-primary | admin.settings.company.domains.make_primary | tenant, auth, verified, web | `CompanyDomainController@makePrimary` | Settings | Domain state machine planı.
| DELETE | /admin/settings/company/domains/{domain} | admin.settings.company.domains.destroy | tenant, auth, verified, web | `CompanyDomainController@destroy` | Settings | Hard delete risk.

