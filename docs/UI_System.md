# UI System Overview

## Directory Layout

```
resources/
├── views/
│   ├── layouts/            # shared shells (admin)
│   ├── partials/           # navbar, sidebar, footer bridges
│   ├── components/ui/      # Blade UI atoms (button, input, table, ...)
│   ├── consoles/           # action consoles (today board prototype)
│   ├── pages/              # neutral pages ready for module adoption
│   ├── legacy/             # backward-compat delegates for old includes
│   └── core/               # existing module views pending migration
├── scss/
│   ├── tokens/             # design tokens (colors, spacing, radius, typography)
│   ├── vendors/            # Bootstrap import orchestration
│   ├── components/         # skinning for existing UI widgets
│   ├── utilities/          # mixins & helper utilities
│   ├── pages/              # page-level overrides (ui-gallery)
│   ├── legacy/             # historical layout rules kept for compatibility
│   ├── admin.scss          # modern admin entry (uses tokens + bootstrap)
│   └── app.scss            # lightweight public entry
└── js/
    ├── admin.js            # admin entry (bootstrap init + runtime bundle)
    ├── admin-runtime.js    # existing runtime initialisers
    ├── app.js              # lightweight public entry hook
    └── bootstrap.js        # axios + Bootstrap helpers
```

## Layouts & Stacks

| Layout | File | Description | Yields | Stacks |
| --- | --- | --- | --- | --- |
| `layouts.admin` | `resources/views/layouts/admin.blade.php` | Modernised admin shell using navbar/sidebar/footer partials and toast stack. | `title`, `module`, `section`, `content` | `page-styles`, `styles`, `page-scripts`, `scripts` |
| `legacy.layouts.admin` | `resources/views/legacy/layouts/admin.blade.php` | Bridge for views still extending the historical layout namespace. | Mirrors parent | Inherits parent stacks |

## Partials

| Partial | File | Purpose | Notes |
| --- | --- | --- | --- |
| `_navbar` | `resources/views/partials/_navbar.blade.php` | Responsive top navigation with action toolbar slot. | Falls back to marketing shortcuts when `@section('navbar-actions')` is absent. |
| `_sidebar` | `resources/views/partials/_sidebar.blade.php` | Shell navigation rail with compact mode compatibility. | Keeps CSS classes expected by existing JS/SCSS, guards undefined dashboard route. |
| `_footer` | `resources/views/partials/_footer.blade.php` | Sticky footer for admin shell. | Accepts `@section('footer-note')` override. |
| `partials.header/sidebar/toast` | `resources/views/partials/*.blade.php` | Legacy includes updated to delegate to the new underscored partials. | Ensures existing includes continue to work. |

## Blade Components

New UI atoms live under `resources/views/components/ui/` and are available via `<x-ui.…>` syntax. See `docs/BladeComponents_Catalog.md` for full API tables.

## Consoles & Pages

* `resources/views/consoles/today.blade.php` now demonstrates rendering the Today Board summary with `<x-ui.table>` and the new layout. The existing rich console at `core/boards/today.blade.php` remains intact and will be migrated module-by-module.
* `resources/views/pages/` reserved for shared neutral pages; existing module views stay under `resources/views/core/` until migrated.

## Asset Entries & Hooks

| Asset | Purpose | Notes |
| --- | --- | --- |
| `resources/scss/admin.scss` | Primary admin stylesheet using ordered Bootstrap imports, design tokens, and legacy shell definitions. | Declares CSS custom properties after Bootstrap to avoid `to-rgb()/var()` collisions. |
| `resources/scss/app.scss` | Lightweight public-facing skin. | Shares tokens without loading module skins. |
| `resources/js/admin.js` | Admin JS entry importing `admin-runtime` plus Bootstrap helpers. | Dispatches `admin:ready` custom event for module hooks. |
| `resources/js/app.js` | Minimal public entry that still initialises axios/Bootstrap and dispatches `app:ready`. | Keeps login/welcome pages lightweight. |

## Bootstrap Import Order

Bootstrap is centralised in `resources/scss/vendors/_bootstrap.scss` via a single `@use "bootstrap/scss/bootstrap";`. The module itself enforces the internal function/variables/maps/mixins sequencing while allowing us to keep all Sass overrides literal.

With CSS custom properties declared **after** the vendor import, the previous `to-rgb()` + `var()` compilation errors are eliminated.

## JavaScript Hooks

* `resources/js/bootstrap.js` initialises axios defaults and binds Bootstrap dropdown/tooltip behaviours.
* `resources/js/admin-runtime.js` retains all existing runtime orchestrations (drawers, modals, density toggles, module init functions) to avoid regressions while the UI is modernised.
* Custom events `app:ready` and `admin:ready` provide progressive enhancement hooks for future module-specific scripts.
