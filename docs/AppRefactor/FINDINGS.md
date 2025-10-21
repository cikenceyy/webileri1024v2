# Findings

## Structural observations
- `app/Bridges` holds systemwide integration events/listeners that belong with the core bus layer rather than a standalone top-level folder.
- The core layer is fragmented across `Core/Auth`, `Core/Http`, `Core/Console`, `Core/Resources`, `Core/Scopes`, and `Core/Traits`. Several of these only provide thin aliases to the newer tenancy classes, creating duplicate namespaces that confuse discovery.
- `Core/Resources/views` is the only reason the `core::` view namespace exists. These templates are pure Blade views that can live under `resources/views/` with modern layouts.
- `Core/Auth/Controllers\LoginController` and `Core/Http/Controllers\DashboardController` are genuine HTTP controllers but sit under core-specific namespaces; they should live under `app/Http/Controllers` beside the other entry-point controllers.
- Console commands currently reside under `app/Core/Console/Commands`. Grouping them under a `Support\Console\Commands` (or similar) folder within Core keeps the top level cleaner and aligns with the target tree.
- Multi-tenant helpers remain in `app/Core/Http/Middleware`, `app/Core/Scopes`, and `app/Core/Traits` as deprecated aliases of the new tenancy implementations. Keeping both increases maintenance cost.
- `app/Domains/Core/Http/Controllers` duplicates functionality already added in `app/Consoles/Http/Controllers`. Routes still import the legacy namespace.
- The top level `app/Support/helpers.php` file is still referenced from composer autoload but contradicts the desired `Core/Support` grouping.
- Company level models (`Company`, `CompanyDomain`) are under `Core/Models`, outside the new `Core/Support` umbrella.
- `ModuleManagerServiceProvider` and `PermissionServiceProvider` pre-date the new Access pipeline; their responsibilities overlap with `AccessServiceProvider` and `ModuleLoaderServiceProvider`.

## Dependency usage
- `app/Core/Http/Middleware/IdentifyTenant`, `app/Core/Scopes/CompanyScope`, and `app/Core/Traits/BelongsToCompany` are only referenced to preserve backwards compatibility. All new code uses the Tenancy namespace.
- Routes in `routes/admin.php` still point at controllers inside `App\Domains\Core\Http\Controllers`, even though new console controllers exist under `app/Consoles/Http/Controllers`.
- Composer still autoloads `app/Support/helpers.php`. Several helpers inside reference numbering and tenancy utilities that were moved to `App\Core\Bus` and `App\Core\Tenancy`.

## Risks
- Removing the alias namespaces without updating imports will break tests and seeds that still refer to the deprecated classes.
- Moving views away from the `core::` namespace requires updating the login controller plus any tests using the old namespace.
- The artisan configuration in `bootstrap/app.php` registers both old and new providers. Cleaning the list must be coordinated with the new namespace layout.
