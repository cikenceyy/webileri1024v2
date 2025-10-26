# TableKit

TableKit provides a shared table infrastructure for admin modules. It exposes a Blade component powered by a lightweight client-side engine for filtering, sorting and pagination. Tables automatically switch between client and server modes depending on the dataset size, and degrade gracefully if JavaScript is unavailable.

## Components

| Component | Description |
| --- | --- |
| `<x-table>` | Root container that renders the table, handles dataset serialisation and exposes accessibility attributes. |
| `<x-table:toolbar>` | Optional toolbar that renders global search, column filters and the per-page selector. Works in both client and server modes. |
| `<x-table:col>` | Internal component used by the toolbar to render type-aware filter inputs. |
| `<x-table:row-actions>` | Renders row-level actions using compact pills. Automatically used by the `actions` column type. |

## Client vs. Server Mode

- **Client mode** is used automatically when the dataset size is ≤ 500 rows. Sorting, filtering and pagination are performed in the browser without network requests. URL query parameters are kept in sync for back/forward navigation.
- **Server mode** is used for larger datasets or when JavaScript is disabled. Toolbar controls fall back to normal GET requests while preserving existing controller logic.

## Accessibility

- Table headers expose `aria-sort` and update dynamically.
- Status messages are announced via `aria-live` when results change.
- Keyboard support allows navigating rows with ↑/↓, activating the primary action with Enter and returning focus to the search input with Esc.
- On mobile breakpoints rows collapse into cards using labels generated from column metadata.

## Usage

```blade
<x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
    <x-slot name="toolbar">
        <x-table:toolbar :config="$tableKitConfig" />
    </x-slot>
</x-table>
```

The view composer injects `$tableKitConfig`, `$tableKitRows` and (if available) `$tableKitPaginator` into the target view. See [COLUMN_CONFIG.md](COLUMN_CONFIG.md) for configuration details and [QUERY_CONTRACT.md](QUERY_CONTRACT.md) for the query string contract.
