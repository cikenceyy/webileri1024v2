# Module Migration – Marketing

## Inventory Mapping
| Old Path | New Path | Type | Notes |
| --- | --- | --- | --- |
| `app/Modules/CrmSales/Config/crm.php` | `app/Modules/Marketing/Config/module.php` | Config | Renamed to `marketing.module`; provider mirrors legacy `crmsales.crm` keys for backwards compatibility.【F:app/Modules/Marketing/Config/module.php†L1-L6】【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L93-L102】 |
| `app/Modules/CrmSales/Config/permissions.php` | `app/Modules/Marketing/Config/permissions.php` | Permissions | Keys normalized to `marketing.*` and merged via AccessServiceProvider catalogue.【F:app/Modules/Marketing/Config/permissions.php†L1-L40】【F:app/Core/Providers/AccessServiceProvider.php†L52-L86】 |
| `app/Modules/CrmSales/Database/**/*` | `app/Modules/Marketing/Database/**/*` | Database | Migrations, factories, and seeders relocated; class names updated to `Marketing*Seeder` with modern config usage.【F:app/Modules/Marketing/Database/migrations/2025_01_01_020000_add_marketing_v2_structures.php†L1-L149】【F:app/Modules/Marketing/Database/seeders/MarketingAdvancedDemoSeeder.php†L1-L188】 |
| `app/Modules/CrmSales/Domain/Models/*.php` | `app/Modules/Marketing/Domain/Models/*.php` | Domain Model | Namespaces switched to marketing and tenancy trait upgraded to `App\Core\Tenancy\Traits\BelongsToCompany`.【F:app/Modules/Marketing/Domain/Models/Customer.php†L1-L64】 |
| `app/Modules/CrmSales/Domain/Observers/*.php` | `app/Modules/Marketing/Domain/Observers/*.php` | Observer | Order observer stays registered through Marketing service provider.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L92】 |
| `app/Modules/CrmSales/Domain/Services/PricingService.php` | `app/Modules/Marketing/Application/Services/PricingService.php` | Service / UseCase | Service moved under Application layer; controllers reference new namespace; default tax sourced from marketing config.【F:app/Modules/Marketing/Application/Services/PricingService.php†L1-L45】 |
| `app/Modules/CrmSales/Http/Controllers/*.php` | `app/Modules/Marketing/Http/Controllers/*.php` | Controller | Route names, middleware, and view calls updated to marketing namespace with module context composer.【F:app/Modules/Marketing/Http/Controllers/OrderController.php†L1-L182】【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L105】 |
| `app/Modules/CrmSales/Http/Requests/*.php` | `app/Modules/Marketing/Http/Requests/*.php` | Form Request | Namespace, authorize, and validation translation paths aligned with Marketing module.【F:app/Modules/Marketing/Http/Requests/StoreCustomerRequest.php†L1-L60】 |
| `app/Modules/CrmSales/Policies/*.php` | `app/Modules/Marketing/Policies/*.php` | Policy | Policies now extend `CompanyOwnedPolicy` with `marketing.*` prefixes and extra abilities (approve/import).【F:app/Modules/Marketing/Policies/OrderPolicy.php†L1-L148】【F:app/Modules/Marketing/Policies/CustomerPolicy.php†L1-L34】 |
| `app/Modules/CrmSales/Providers/CrmSalesServiceProvider.php` | `app/Modules/Marketing/Providers/MarketingServiceProvider.php` | Service Provider | Registers policies, observers, config merge, and compat aliases for views/translations/config.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L1-L105】 |
| `app/Modules/CrmSales/Resources/views/**/*` | `app/Modules/Marketing/Resources/views/**/*` | Blade Views | Layout upgraded to `layouts.admin`, namespace exported as `marketing::`; view composer injects module/page defaults while legacy `crmsales::` alias remains.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L105】【F:app/Modules/Marketing/Resources/views/orders/index.blade.php†L1-L89】 |
| `app/Modules/CrmSales/Routes/admin.php` | `app/Modules/Marketing/Routes/admin.php` | Routes (Admin) | Prefix changes to `/admin/marketing`, names `admin.marketing.*`, includes demo index route and tenant middleware.【F:app/Modules/Marketing/Routes/admin.php†L1-L52】 |
| (none) | `app/Modules/Marketing/Routes/api.php` | Routes (API) | Placeholder for future API endpoints with tenant/auth guards.【F:app/Modules/Marketing/Routes/api.php†L1-L9】 |
| `app/Modules/CrmSales/Resources/js/*` + `resources/js/modules/crm-sales.js` | `app/Modules/Marketing/Resources/js/marketing.js` | JS Entry | Dynamic loader handles marketing chunk; legacy module script removed after porting line editor & guards.【F:app/Modules/Marketing/Resources/js/marketing.js†L1-L156】【F:resources/js/admin-runtime.js†L1-L78】 |
| `resources/scss/modules/_crm-sales.scss` | `app/Modules/Marketing/Resources/scss/marketing.scss` | SCSS | Styles co-located with module, dual data attributes for compatibility; global partial removed.【F:app/Modules/Marketing/Resources/scss/marketing.scss†L1-L36】 |

## Route Remapping
_All CRM/Sales admin routes now resolve under `/admin/marketing` with `admin.marketing.*` names. The ModuleLoader discovers the route file automatically while the marketing service provider adds policy bindings._【F:app/Modules/Marketing/Routes/admin.php†L1-L52】【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L92】

## View and Translation Namespace Plan
- Blade namespaces `crmsales::` and `crm::` remain aliased to the marketing views until downstream consumers migrate.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L83】
- Translation namespace aliases mirror the same behaviour, enabling `trans('crm::…')` calls during transition.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L85-L92】
- Route helpers should switch to `route('admin.marketing.*')`; alias mapping in `config/modules.php` keeps `CrmSales` pointing to `Marketing` for reflection-based discovery.【F:config/modules.php†L1-L8】

## Dependencies & Notes
- Pricing service relocated to `Application\Services` and imported by controllers/views; dynamic totals rely on the marketing module JS bundle.【F:app/Modules/Marketing/Application/Services/PricingService.php†L1-L45】【F:app/Modules/Marketing/Resources/js/marketing.js†L1-L156】
- Policies leverage `CompanyOwnedPolicy` for company guard + permission prefix, keeping approve/import extras where required.【F:app/Modules/Marketing/Policies/OrderPolicy.php†L1-L148】
- Marketing views auto-assign `$module`/`$page` context via composer to feed the lazy-loader without touching each Blade file.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L105】
- Legacy JS/SCSS entries were removed from the global bundle; module assets now ship exclusively through dynamic imports for smaller admin payloads.【F:resources/js/admin-runtime.js†L1-L78】【F:resources/scss/admin.scss†L1-L28】
