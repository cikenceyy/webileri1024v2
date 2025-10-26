# TableKit

TableKit provides a shared table infrastructure for admin modules. It exposes a Blade component powered by a lightweight client-side engine for filtering, sorting and pagination. Tables automatically switch between client and server modes depending on the dataset size, and degrade gracefully if JavaScript is unavailable. The visual language follows the 80/20 rules adopted for the admin UI: compact 12/14px typography, 16px paddings on desktop (8px on handheld), sticky headers/first columns and right-aligned number/date columns.

## Components

| Component | Description |
| --- | --- |
| `<x-table>` | Root container that renders the table, handles dataset serialisation and exposes accessibility attributes. Accepts `dense="true"`, `filters="status,warehouse"` and an optional `empty` slot for bespoke blank states. |
| `<x-table:toolbar>` | Optional toolbar that renders global search, column filters and the per-page selector. Works in both client and server modes. |
| `<x-table:col>` | Internal component used by the toolbar to render type-aware filter inputs. |
| `<x-table:row-actions>` | Renders row-level actions using compact pills. Automatically used by the `actions` column type. |
| `<x-table:stepper-summary>` | Optional slot rendered above the toolbar. Mirrors console steppers and exposes live counters. |
| `<x-table:bulk>` | Optional slot rendered in the footer. Displays bulk actions alongside the live selection counter. |
| `<x-table:row-meta>` | Optional template used to render additional per-row metadata beneath the first column (chips, signals, etc.). |

## Client vs. Server Mode

- **Client mode** is used automatically when the dataset size is ≤ `TABLEKIT_THRESHOLD` (default 500). Sorting, filtering and pagination are performed in the browser without network requests. URL query parameters are kept in sync for back/forward navigation.
- **Server mode** is used for larger datasets or when JavaScript is disabled. Toolbar controls fall back to normal GET requests while preserving existing controller logic. Server pagination links remain intact.
- **Virtual mode** piggybacks on client mode and activates automatically when `virtual` is enabled in the column config. Rendering is virtualised using fixed row heights so even 10k+ rows remain responsive.

## Accessibility

- Table headers expose `aria-sort` and update dynamically.
- Status messages are announced via `aria-live` when results change.
- Keyboard support allows navigating rows with ↑/↓, activating the primary action with Enter and returning focus to the search input with Esc. Global shortcuts include `/` (focus search), `A` (toggle select all) and `P` (emit the bulk print shortcut event).
- On mobile breakpoints rows collapse into cards using labels generated from column metadata.

## Usage

```blade
<x-table :config="$tableKitConfig"
         :rows="$tableKitRows"
         :paginator="$tableKitPaginator"
         dense="true"
         filters="status,customer,warehouse,date">
    <x-slot name="toolbar">
        <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('Müşteri ara…')" />
    </x-slot>

    <x-slot name="empty">
        <div class="tablekit__empty-content">
            <div class="tablekit__empty-content-icon">📦</div>
            <div>{{ __('Henüz kayıt bulunmuyor.') }}</div>
        </div>
    </x-slot>
</x-table>
```

The view composer injects `$tableKitConfig`, `$tableKitRows` and (if available) `$tableKitPaginator` into the target view. See [COLUMN_CONFIG.md](COLUMN_CONFIG.md) for configuration details, [QUERY_CONTRACT.md](QUERY_CONTRACT.md) for the query string contract, [CONSOLES_INTEGRATION.md](CONSOLES_INTEGRATION.md) for console-specific guidance and [PRESETS.md](PRESETS.md) for reusable column sets.

### Dense mode

Adding `dense="true"` reduces the vertical rhythm by ~2px and tightens typography. Use it for high-density data tables (inventory snapshots, production queues) where the row meta remains legible but space is at a premium.

### Empty states

Provide an `empty` slot (see example above) whenever the page needs contextual CTAs. Slots render inside the default scaffold so blank states remain aligned with the rest of the design.
