# Model Inventory

Bu tablo çok-tenant (`company_id`) stratejisine uyum, SoftDeletes kullanımı ve Core/Module ayrımları açısından modelleri listeler.

| Model | Modül/Katman | Tablonun Tahmini Adı | `company_id` Kolonu | İndeks Durumu | SoftDeletes | Traitler | Öneri |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `App\Models\User` | Core/Auth | `users` | Var (`$fillable`) | `database/migrations/0001_01_01_000000_create_users_table.php` şirket FK + index içeriyor | Yok | `HasFactory`, `Notifiable`, `AppliesRoles` | BelongsToCompany trait'i eklenip global scope ile hizalanmalı; Spatie team scope ile uyum test edilmeli. |
| `App\Core\Models\Company` | Core | `companies` | Yok (root entity) | Domain unique index `companies.domain` + theme alanları eklenmiş | Yok | `HasFactory` | Tenant switcher servislerine provider eklenmeli. |
| `App\Core\Models\CompanyDomain` | Core | `company_domains` | Var (FK) | `company_id` foreign key + unique domain | Yok | `HasFactory` | Domain doğrulama job'ı planlanmalı. |
| `App\Modules\Drive\Domain\Models\Media` | Drive | `media` | Var (`BelongsToCompany`) | FK + index (migrationlar Drive modülü) | Var | `BelongsToCompany`, `SoftDeletes` | SoftDeletes kaldırma planında arşiv tablosu oluşturulmalı. |
| `App\Modules\Inventory\Domain\Models\Product` | Inventory | `products` | Var | Migration `unique(['company_id','sku'])`, `status` index | Var | `BelongsToCompany`, `SoftDeletes` | SoftDeletes → arşiv/publish state; `media_id` null on delete kontrolü sürdürülmeli. |
| `App\Modules\Inventory\Domain\Models\ProductVariant` | Inventory | `product_variants` | Var | `company_id`,`product_id` index'leri mevcut | Var | `BelongsToCompany`, `SoftDeletes` | Variant senkronizasyonu domain servisine taşınmalı. |
| `App\Modules\Inventory\Domain\Models\ProductCategory` | Inventory | `product_categories` | Var | `parent_id` nullable, `company_id` index | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete kaldırılınca nested set yerine path yaklaşımı değerlendirilmeli. |
| `App\Modules\Inventory\Domain\Models\ProductGallery` | Inventory | `product_gallery` | Var | `company_id`,`product_id` index'leri | Yok | `BelongsToCompany` | Drive medya doğrulaması zorunlu. |
| `App\Modules\Inventory\Domain\Models\PriceList` | Inventory | `price_lists` | Var | `company_id`,`code` unique | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete yerine state column (`status`) eklenmeli. |
| `App\Modules\Inventory\Domain\Models\PriceListItem` | Inventory | `price_list_items` | Var | `company_id`,`price_list_id` index | Yok | `BelongsToCompany` | Index'ler composite; ledger sorguları optimize edilmeli. |
| `App\Modules\Inventory\Domain\Models\StockItem` | Inventory | `stock_items` | Var | `company_id`,`warehouse_id` index planı | Yok | `BelongsToCompany` | Movement aggregate ile senkron tutulmalı. |
| `App\Modules\Inventory\Domain\Models\StockMovement` | Inventory | `stock_movements` | Var | `company_id`,`stock_item_id` index | Yok | `BelongsToCompany` | Ledger raporu için view materialization değerlendirilmeli. |
| `App\Modules\Inventory\Domain\Models\Unit` | Inventory | `units` | Var | `company_id`,`code` unique | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete kaldırılınca `is_archived` kolonu eklenmeli. |
| `App\Modules\Inventory\Domain\Models\Warehouse` | Inventory | `warehouses` | Var | `company_id`,`code` unique | Var | `BelongsToCompany`, `SoftDeletes` | Silme yerine `status` alanı kullanılmalı. |
| `App\Modules\Logistics\Domain\Models\Shipment` | Logistics | `shipments` | Var | `company_id`,`shipment_no` unique | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete yerine `canceled_at` alanı önerilir. |
| `App\Modules\Finance\Domain\Models\Invoice` | Finance | `invoices` | Var | `company_id`,`number` unique + `status` index | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete kaldırılarak `status=voided` modeli uygulanmalı. |
| `App\Modules\Finance\Domain\Models\InvoiceLine` | Finance | `invoice_lines` | Var | `company_id`,`invoice_id` index | Yok | `BelongsToCompany` | Toplamların denormalizasyonu için triggers planlanmalı. |
| `App\Modules\Finance\Domain\Models\Receipt` | Finance | `receipts` | Var | `company_id`,`receipt_no` unique | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete kalkınca `voided_at` alanı eklenmeli. |
| `App\Modules\Finance\Domain\Models\BankAccount` | Finance | `bank_accounts` | Var | `company_id`,`iban` unique | Yok | `BelongsToCompany` | IBAN doğrulaması Core helper'a taşınmalı. |
| `App\Modules\Finance\Domain\Models\BankTransaction` | Finance | `bank_transactions` | Var | `company_id`,`bank_account_id` index | Yok | `BelongsToCompany` | Mutlak tutar denormalizasyonu planlanmalı. |
| `App\Modules\Finance\Domain\Models\Allocation` | Finance | `allocations` | Var | `company_id`,`invoice_id` index | Yok | `BelongsToCompany` | Allocate işlemleri idempotent hale getirilmeli. |
| `App\Modules\Finance\Domain\Models\ApInvoice` | Finance | `ap_invoices` | Var | `company_id`,`vendor_invoice_no` index | Yok | `BelongsToCompany` | Vendor entity Settings modülüne taşınmalı. |
| `App\Modules\Finance\Domain\Models\ApInvoiceLine` | Finance | `ap_invoice_lines` | Var | `company_id`,`ap_invoice_id` index | Yok | `BelongsToCompany` |  Decimal scale doğrulaması yapılmalı. |
| `App\Modules\Finance\Domain\Models\ApPayment` | Finance | `ap_payments` | Var | `company_id`,`payment_no` index | Yok | `BelongsToCompany` | Bank transaction ile senkron kontrolü. |
| `App\Modules\Production\Domain\Models\WorkOrder` | Production | `work_orders` | Var | `company_id`,`doc_no` unique | Yok | `BelongsToCompany` | Work order kapanışında SoftDeletes yok; status state machine eklenmeli. |
| `App\Modules\Production\Domain\Models\WorkOrderIssue` | Production | `work_order_issues` | Var | `company_id`,`work_order_id` index | Yok | `BelongsToCompany` | Issue satırları Inventory ledger ile senkron tutulur. |
| `App\Modules\Production\Domain\Models\WorkOrderReceipt` | Production | `work_order_receipts` | Var | `company_id`,`work_order_id` index | Yok | `BelongsToCompany` | Receipt akışı Finance entegrasyonuna hazırlanmalı. |
| `App\Modules\Procurement\Domain\Models\PurchaseOrder` | Procurement | `purchase_orders` | Var | `company_id`,`po_number` unique | Yok | `BelongsToCompany` | Soft delete bulunmuyor; status alanı var mı kontrol edilip workflow eklenmeli. |
| `App\Modules\Procurement\Domain\Models\PoLine` | Procurement | `po_lines` | Var | `company_id`,`purchase_order_id` index | Yok | `BelongsToCompany` | Inventory rezervasyonuyla bağ kurulacak. |
| `App\Modules\Procurement\Domain\Models\Grn` | Procurement | `grns` | Var | `company_id`,`grn_no` unique | Yok | `BelongsToCompany` | Goods receipt -> stock movement otomasyonu doğrulanmalı. |
| `App\Modules\Procurement\Domain\Models\GrnLine` | Procurement | `grn_lines` | Var | `company_id`,`grn_id` index | Yok | `BelongsToCompany` | Lot/serial desteği planlanmalı. |
| `App\Modules\Marketing\Domain\Models\Customer` | Marketing (eski CRM) | `customers` | Var | `company_id`,`code` unique | Var | `BelongsToCompany`, `SoftDeletes` | Modül rename sonrası `marketing_customers` prefix'i değerlendirilmeli. |
| `App\Modules\Marketing\Domain\Models\CustomerContact` | Marketing | `customer_contacts` | Var | `company_id`,`customer_id` index | Yok | `BelongsToCompany` | Kişi kartı Settings modülüne bağlanmalı. |
| `App\Modules\Marketing\Domain\Models\CustomerAddress` | Marketing | `customer_addresses` | Var | `company_id`,`customer_id` index | Yok | `BelongsToCompany` | Lokasyon servisleri ile entegre edilecek. |
| `App\Modules\Marketing\Domain\Models\Opportunity` | Marketing | `opportunities` | Var | `company_id`,`code` unique | Var | `BelongsToCompany`, `SoftDeletes` | Pipeline stage history tablosu eklenmeli. |
| `App\Modules\Marketing\Domain\Models\Quote` | Marketing | `quotes` | Var | `company_id`,`quote_no` unique | Var | `BelongsToCompany`, `SoftDeletes` | Hard delete kalktığında `voided_at` kullanılmalı. |
| `App\Modules\Marketing\Domain\Models\QuoteLine` | Marketing | `quote_lines` | Var | `company_id`,`quote_id` index | Yok | `BelongsToCompany` | Pricing pipeline Inventory ile hizalanmalı. |
| `App\Modules\Marketing\Domain\Models\Order` | Marketing | `orders` | Var | `company_id`,`order_no` unique | Var | `BelongsToCompany`, `SoftDeletes` | Soft delete kalkınca `canceled_at` alanı kullanılmalı. |
| `App\Modules\Marketing\Domain\Models\OrderLine` | Marketing | `order_lines` | Var | `company_id`,`order_id` index | Yok | `BelongsToCompany` | Shipment senkronu Logistics modülü ile hizalanmalı. |
| `App\Modules\Marketing\Domain\Models\Activity` | Marketing | `activities` | Var | `company_id`,`customer_id` index | Yok | `BelongsToCompany` | Timeline API'si modülerleştirilmeli. |
| `App\Modules\Marketing\Domain\Models\Note` | Marketing | `notes` | Var | `company_id`,`customer_id` index | Yok | `BelongsToCompany` | Soft delete yok; text arama için full-text index değerlendirilmeli. |
| `App\Modules\Marketing\Domain\Models\Attachment` | Marketing | `attachments` | Var | `company_id`,`customer_id` index | Yok | `BelongsToCompany` | Drive ile referans bütünlüğü güçlendirilmeli. |

## Gözlemler
- Modül modellerinin tamamı `BelongsToCompany` trait'i ile şirket scopes'una dahil. Çekirdek `User` modeli dışında SoftDeletes kullanılmıyor.
- SoftDeletes kullanan modeller (Inventory, Finance, Logistics, Marketing) ileride kaldırılacak; tablo yapıları `deleted_at` kolonuna bağlı. Kaldırma sırasında raporlar ve unique indexler yeniden değerlendirilmelidir.
- Migration'lar `foreignId('company_id')->constrained()->cascadeOnDelete()` pattern'i ile yazılmış; `database/migrations/2025_01_01_010600_normalize_company_indexes.php` global index optimizasyonu içeriyor.
- Spatie Permission konfigürasyonu `company_id` üzerinden team scope kullanıyor (`config/permission.php`).

