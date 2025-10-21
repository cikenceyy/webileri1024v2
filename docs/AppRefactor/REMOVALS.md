# Removals

| Path | Reason |
| --- | --- |
| `app/Domains/Core/Http/Controllers/Boards/TodayBoardController.php` | Legacy controller superseded by `App\Consoles\Http\Controllers\TodayBoardController` and modern layouts. |
| `app/Domains/Core/Http/Controllers/Consoles/MtoConsoleController.php` | Replaced by the orchestrator-backed `App\Consoles\Http\Controllers\MTOController`. |
| `app/Domains/Core/Http/Controllers/Consoles/P2pConsoleController.php` | Replaced by `App\Consoles\Http\Controllers\P2PController`. |
| `app/Core/Http/Middleware/IdentifyTenant.php` | Deprecated alias removed; all consumers updated to `App\Core\Tenancy\Middleware\IdentifyTenant`. |
| `app/Core/Scopes/CompanyScope.php` | Deprecated alias removed in favour of `App\Core\Tenancy\Scopes\CompanyScope`. |
| `app/Core/Traits/BelongsToCompany.php` | Deprecated alias removed in favour of `App\Core\Tenancy\Traits\BelongsToCompany`. |
| `app/Core/Providers/PermissionServiceProvider.php` | Access catalogue merged into `AccessServiceProvider`; duplicate provider removed. |
| `app/Core/Providers/ModuleManagerServiceProvider.php` | Module provider auto-registration merged into `ModuleLoaderServiceProvider`. |

> ℹ️ Run `composer dump-autoload` after deploying so the updated helper path is picked up by Composer.
