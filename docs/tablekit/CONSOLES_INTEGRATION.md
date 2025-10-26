# Consoles Integration

TableKit v1.9 powers the console flow lists (O2C, P2P, Replenish, etc.) while keeping the existing stepper navigation and bulk action forms intact. This document describes the conventions required for console views.

## Layout Slots

| Slot | Purpose |
| --- | --- |
| `<x-table:stepper-summary>` | Render the condensed counter bar that mirrors the left-hand stepper. Use the same ordering and data attributes (`data-step-target`) so TableKit can synchronise keyboard navigation. Add `aria-controls` to mirror the stepper container. |
| `<x-table:bulk>` | Place existing bulk action buttons inside this slot. The selection counter is rendered automatically (`data-tablekit-bulk-count`). |
| `<x-table:row-meta>` | Template for secondary row information (chips, signals, warehouse badges). The template is injected beneath the first column in both server and client modes. |

## Dataset Requirements

- Each row **must** expose a stable `id` so selection and bulk actions can reference it.
- Selection uses a `select` column type. Provide `['value' => $id, 'disabled' => false, 'checked' => false]` to control availability.
- Meta fragments can be pre-rendered via `view('components.tablekit.row-meta', [...])->render()` or inline HTML.
- Enable virtual scrolling (`'virtual' => true`) for console datasets to keep scrolling fluid when thousands of records are present. Adjust `row_height` when dense layouts are required (default 48px, dense mode works well with 44px).
- When using dense tables add `dense="true"` to `<x-table>` so keyboard focus outlines still align with the condensed rows.

## Query Parameters

Console tables exchange the following parameters with the toolbar and stepper:

- `step` – currently focused stepper bucket (`orders`, `shipments`, `returns`, ...).
- `status[]` – multi-select status filters scoped to the active step.
- Standard parameters (`q`, `sort`, `filters[...]`, `perPage`) continue to work and are merged with console-specific keys.

Client mode keeps these parameters in sync via `history.replaceState` so page refreshes or shareable URLs work as expected. Server mode simply forwards them as GET parameters.

## Keyboard Shortcuts

| Shortcut | Behaviour |
| --- | --- |
| `/` | Focuses the global search input. |
| `A` | Toggles “select all” for the currently visible rows. |
| `Enter` | Triggers the primary row action (if any). |
| `P` | Dispatches the `tablekit:shortcut` event with `{ key: 'print', selected: [...] }`. Existing console scripts can listen for this event to trigger print flows. |
| `Shift` + `S` | Synchronises the stepper selection with the toolbar filter when both are visible. |
| `Esc` | Returns focus to the search input. |

## Bulk Actions

The `<x-table:bulk>` slot renders inside the footer. Use standard buttons or forms – the selection counter is exposed via `[data-tablekit-bulk-count]`. To access the current selection in JavaScript listen for the `tablekit:selection` custom event emitted whenever the selection changes.

```
document.querySelector('[data-tablekit]').addEventListener('tablekit:selection', (event) => {
    console.log(event.detail.selected); // array of IDs
});
```

## Progressive Enhancement

- Without JavaScript the server-rendered markup remains identical to previous console tables; checkboxes and forms continue to work.
- When JavaScript loads, TableKit enhances the table by wiring keyboard navigation, live counters and virtual scrolling.
- Any console-specific scripts should wait for the `tablekit:ready` event before reading dataset metadata.
