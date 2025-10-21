# Move map (applied)

| Source | Action | Target/Notes |
| --- | --- | --- |
| `app/Bridges/Events/*.php` | moved | `app/Core/Bus/Events/*.php` |
| `app/Bridges/Listeners/*.php` | moved | `app/Core/Bus/Listeners/*.php` |
| `app/Core/Auth/Controllers/LoginController.php` | moved | `app/Http/Controllers/Auth/LoginController.php` |
| `app/Core/Http/Controllers/DashboardController.php` | moved | `app/Http/Controllers/Admin/DashboardController.php` |
| `app/Core/Console/Commands/*.php` | moved | `app/Core/Support/Console/Commands/*.php` |
| `app/Core/Resources/views/login.blade.php` | moved | `resources/views/auth/login.blade.php` |
| `app/Core/Resources/views/dashboard.blade.php` | moved | `resources/views/admin/dashboard.blade.php` |
| `app/Core/Models/Company*.php` | moved | `app/Core/Support/Models/Company*.php` |
| `app/Support/helpers.php` | moved | `app/Core/Support/Helpers/helpers.php` |
| `app/Domains/Core/Http/Controllers/**` | removed | Replaced by `App\Consoles\Http\Controllers\*` |
| `app/Core/Http/Middleware/IdentifyTenant.php` | removed | Deprecated alias dropped; use `App\Core\Tenancy\Middleware\IdentifyTenant` |
| `app/Core/Scopes/CompanyScope.php` | removed | Deprecated alias dropped |
| `app/Core/Traits/BelongsToCompany.php` | removed | Deprecated alias dropped |
| `app/Core/Providers/PermissionServiceProvider.php` | removed | Access logic handled by `AccessServiceProvider` |
| `app/Core/Providers/ModuleManagerServiceProvider.php` | removed | Module provider discovery merged into `ModuleLoaderServiceProvider` |
