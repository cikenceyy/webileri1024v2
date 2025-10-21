# Tenancy How-To

## Adding a Tenant-Aware Table
1. Create the migration under the relevant module directory and ensure the table includes:
   - `foreignId('company_id')->constrained('companies')->cascadeOnDelete();`
   - Composite unique keys that include `company_id` for business identifiers (e.g. SKU, code, number).
   - Supporting indexes prefixed with `company_id` for status/date lookups.【F:app/Modules/Inventory/Database/migrations/2024_01_01_000500_create_products_table.php†L12-L33】【F:app/Modules/Finance/Database/migrations/2025_01_01_030000_create_invoices_table.php†L12-L37】
2. For join tables, store both the parent key and `company_id`; if the parent already carries `company_id`, backfill with a migration statement or observer before tightening the column to `NOT NULL`.
3. Run `php artisan migrate` followed by `php artisan webileri:tenancy:audit` to confirm no `NULL company_id` rows remain.【F:app/Core/Tenancy/Console/Commands/TenancyAuditCommand.php†L17-L110】

## Creating a Tenant-Aware Model
1. Use `use App\Core\Tenancy\Traits\BelongsToCompany;` on the model. The trait automatically attaches the company scope and fills `company_id` on create.【F:app/Core/Tenancy/Traits/BelongsToCompany.php†L5-L28】
2. Add the `company` relation when you need to eager-load tenant metadata; the trait already exposes a `belongsTo` helper.
3. When seeding or creating records outside HTTP (queues, CLI), bind the company manually: `app()->instance('company', $company);` or call `request()->attributes->set('company_id', $company->id);` before creating the model.【F:tests/Feature/Tenancy/TenancyScopeTest.php†L21-L39】
4. Use `Model::withoutGlobalScopes()` only in migrations or background jobs where cross-tenant access is intentional and audited.【F:tests/Feature/Tenancy/TenancyScopeTest.php†L41-L46】

## Middleware & Routes
- Apply the `tenant` alias provided by `TenancyServiceProvider` to all authenticated web routes (admin, consoles, module admin routes).【F:app/Core/Providers/TenancyServiceProvider.php†L9-L19】【F:app/Consoles/Routes/console.php†L6-L11】【F:app/Modules/Marketing/Routes/admin.php†L7-L11】
- For API endpoints, include `tenant` alongside `auth:sanctum` or other guards before returning tenant data.

## Factories & Seeders
- Factories should default the `company_id` to a `Company::factory()` or accept overrides through states.【F:database/factories/UserFactory.php†L5-L33】【F:database/factories/Core/CompanyFactory.php†L5-L24】
- Seeders that create tenant demo data must wrap their execution in an environment guard and configure `PermissionRegistrar` için `company_id` bağlamı. `RolesAndPermissionsSeeder` yalnızca local/localhost URL’lerinde çalışır, şirket kaydı bulunmuyorsa demo şirket oluşturur ve rollerin izin setlerini senkronize eder.【F:database/seeders/RolesAndPermissionsSeeder.php†L1-L141】
- AccessServiceProvider cache’i yalnızca CLI senaryolarında sıfırlar; yeni izin ekledikten sonra `php artisan optimize:clear` veya `php artisan permissions:cache-reset` gerekmez.

## Access & Policies
- `AccessServiceProvider` merkezi `config/permissions.php` ile modül bazlı `Config/permissions.php` dosyalarını birleştirir ve aktif tenant için Spatie registrar team id’sini set eder.【F:app/Core/Providers/AccessServiceProvider.php†L5-L95】【F:config/permissions.php†L1-L47】【F:app/Modules/Marketing/Config/permissions.php†L1-L7】
- Rollerde `biz` Gate::before kısa devresiyle süper yetkili, `patron` tam yazma, `muhasebeci` finans yazma + diğer modüller view, `stajyer` yalnızca view/index/show izinlerine sahiptir (seeder’da tanımlı matris).【F:database/seeders/RolesAndPermissionsSeeder.php†L89-L137】
- Yeni policy yazarken `App\Core\Access\Policies\CompanyOwnedPolicy` sınıfını extend edip `$permissionPrefix` belirlemek cross-tenant kaçaklarını engeller. Yetki kontrolünün ilk satırında şirket eşitliği korunur.【F:app/Core/Access/Policies/CompanyOwnedPolicy.php†L5-L49】

## Testing & Auditing
1. Feature tests can simulate tenancy by binding the company and setting the request attribute before invoking application code.【F:tests/Feature/Tenancy/TenancyScopeTest.php†L21-L39】
2. The `webileri:tenancy:audit` command logs `NULL company_id` rows and cross-tenant mismatches under `storage/logs/tenancy_audit.log`. Review the log after deployments and before enabling new modules.【F:app/Core/Tenancy/Console/Commands/TenancyAuditCommand.php†L17-L110】
3. For manual verification, inspect the log and rerun with `--log=/tmp/custom.log` in CI pipelines.

