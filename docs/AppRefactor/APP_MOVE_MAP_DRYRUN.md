# Planned move map (dry run)

| Source | Action | Target |
| --- | --- | --- |
| `app/Bridges/Events/*.php` | move | `app/Core/Bus/Events/*.php` |
| `app/Bridges/Listeners/*.php` | move | `app/Core/Bus/Listeners/*.php` |
| `app/Core/Auth/Controllers/LoginController.php` | move | `app/Http/Controllers/Auth/LoginController.php` |
| `app/Core/Http/Controllers/DashboardController.php` | move | `app/Http/Controllers/Admin/DashboardController.php` |
| `app/Core/Console/Commands/*.php` | move | `app/Core/Support/Console/Commands/*.php` |
| `app/Core/Resources/views/login.blade.php` | move | `resources/views/auth/login.blade.php` |
| `app/Core/Resources/views/dashboard.blade.php` | move | `resources/views/admin/dashboard.blade.php` |
| `app/Core/Models/Company*.php` | move | `app/Core/Support/Models/Company*.php` |
| `app/Support/helpers.php` | move | `app/Core/Support/Helpers/helpers.php` |
| `app/Domains/Core/Http/Controllers/**` | remove | superseded by `app/Consoles/Http/Controllers` |
| `app/Core/Http/Middleware/IdentifyTenant.php` | remove | use `App\Core\Tenancy\Middleware\IdentifyTenant` |
| `app/Core/Scopes/CompanyScope.php` | remove | use `App\Core\Tenancy\Scopes\CompanyScope` |
| `app/Core/Traits/BelongsToCompany.php` | remove | use `App\Core\Tenancy\Traits\BelongsToCompany` |
| `app/Core/Providers/PermissionServiceProvider.php` | remove | functionality handled by `AccessServiceProvider` |
| `app/Core/Providers/ModuleManagerServiceProvider.php` | inline | merge auto-registration into `ModuleLoaderServiceProvider` |
