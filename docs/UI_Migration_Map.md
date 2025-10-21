# UI Migration Map

## Layouts & Partials

| Legacy Path | New Destination | Status | Notes |
| --- | --- | --- | --- |
| `resources/views/layouts/admin.blade.php` | `resources/views/layouts/admin.blade.php` | Rebuilt | New layout consumes `_navbar`, `_sidebar`, `_footer`, keeps legacy stacks `page-styles` & `page-scripts`. |
| `resources/views/legacy/layouts/admin.blade.php` | `resources/views/layouts/admin.blade.php` | Bridge | Allows `@extends('legacy.layouts.admin')` to inherit the new shell without code changes. |
| `resources/views/partials/header.blade.php` | `resources/views/partials/_navbar.blade.php` | Delegated | Old include now proxies to the modern navbar partial. |
| `resources/views/partials/sidebar.blade.php` | `resources/views/partials/_sidebar.blade.php` | Delegated | Keeps historical CSS hooks while exposing Bootstrap Icons. |
| `resources/views/partials/toast.blade.php` | `<x-ui.toast-stack>` | Delegated | Legacy container now wraps the reusable toast stack component. |

## Components

| Legacy Usage | New Component | Migration Notes |
| --- | --- | --- |
| `<x-ui-button>` | `<x-ui.button>` | New Blade anonymous component provides variant/size/icon API; legacy component remains for compatibility. |
| `<x-ui-input>` | `<x-ui.input>` | Standardised input wrapper with validation feedback. |
| `<x-ui-select>` | `<x-ui.select>` | Provides placeholder, option binding, and error state. |
| `<x-ui-table>` (legacy hyphen) | `<x-ui.table>` | Table component exposes `thead`/`tbody` slots and optional responsive wrapper. |
| `<x-ui-modal>` | `<x-ui.modal>` | Bootstrap-compatible modal skeleton. |
| `partials.toast` inline markup | `<x-ui.toast>` & `<x-ui.toast-stack>` | Componentised toast stack retains existing JS hooks (`data-ui="toast-container"`). |

## Assets

| Legacy Entry | New Entry | Transition Notes |
| --- | --- | --- |
| `resources/scss/app.scss` (monolithic) | `resources/scss/admin.scss` | Tokens + Bootstrap orchestrated via `vendors/_bootstrap.scss`; legacy shell CSS extracted to `legacy/_admin.scss` and imported post-bootstrap. |
| n/a | `resources/scss/app.scss` (public) | Lightweight theme for login/welcome; shares tokens without heavy module styles. |
| `resources/js/app.js` (monolithic) | `resources/js/admin.js` + `resources/js/admin-runtime.js` | Runtime moved into `admin-runtime.js`; new admin entry dispatches `admin:ready`. |
| n/a | `resources/js/app.js` (public) | Minimal entry dispatching `app:ready` while keeping axios defaults. |

## Consoles & Pages

| Legacy View | New Target | Action |
| --- | --- | --- |
| `resources/views/core/boards/today.blade.php` | `resources/views/consoles/today.blade.php` | New summary view showcases the future layout. Controller still renders legacy board; migration will happen per module. |
| `resources/views/core/consoles/**` | `resources/views/consoles/**` | Reserved slots for future command consoles; legacy files remain until module migration. |

## Find & Replace Helpers

```
@extends('layouts.admin')        → (no change, new layout already loaded)
@extends('legacy.layouts.admin')  → leave as-is; bridge forwards to new layout
@include('partials.header')        → now renders `_navbar`
@include('partials.sidebar')       → now renders `_sidebar`
@include('partials.toast')         → now renders `<x-ui.toast-stack>`
```

Modules can gradually swap `@extends('legacy.layouts.admin')` with `@extends('layouts.admin')` once verified.
