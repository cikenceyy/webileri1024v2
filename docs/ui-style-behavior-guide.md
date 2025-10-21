# UI Style & Behavior Guide

## Tokens

### Spacing
| Token | Value | Notes |
| --- | --- | --- |
| `--ui-space-1` | 8px | Micro gaps, icon separation |
| `--ui-space-2` | 16px | Default vertical rhythm inside cards |
| `--ui-space-3` | 24px | Card-to-card spacing, dense gutters |
| `--ui-space-4` | 32px | Section gutters |
| `--ui-space-5/6/7` | 40 / 48 / 64px | Page-level breathing room |

### Motion
| Token | Value | Usage |
| --- | --- | --- |
| `--t-fast` | 120ms | Hover, inline feedback |
| `--t-med` | 200ms | Drawer & modal transitions |
| `--t-slow` | 260ms | Toast stack, density fades |
| `--ease-soft` | cubic-bezier(.22,1,.36,1) | Primary interaction easing |
| `--ease-out` | cubic-bezier(.16,.84,.44,1) | Hover + toggle easing |
| Distances | Drawer 12px / Modal 8px | Axis-aligned translations only |

Reduced motion is applied when either `prefers-reduced-motion` matches or `<html data-motion="reduced">` is set. Under that state, transition durations collapse to 0ms and `motion-runtime` freezes list animations.

### Elevation & Blur
| Token | Value | Intent |
| --- | --- | --- |
| `--ui-shadow-xs` | `0 1px 2px rgba(15, 23, 42, 0.04)` | Card baseline |
| `--ui-shadow-md` | `0 8px 24px var(--ui-color-shadow)` | Overlays & floating surfaces |
| `--ui-blur-header` | `blur(16px)` | Header blur once scroll exceeds 8px |

### Z-Index Ladder
| Layer | Token | Value |
| --- | --- | --- |
| Sidebar | `--ui-z-sidebar` | 800 |
| Header | `--ui-z-header` | 900 |
| Dropdowns | `--ui-z-dropdown` | 1000 |
| Drawer | `--ui-z-drawer` | 1100 |
| Modal | `--ui-z-modal` | 1200 |
| Toast | `--ui-z-toast` | 1300 |
| Tooltip | `--ui-z-tooltip` | 1400 |

## Movement Grammar
- **Drawer** – translateX 12px + opacity, `var(--t-med)` with `--ease-soft`. Frozen lists via `ui:overlay:open` events.
- **Modal** – translateY 8px + opacity, `var(--t-med)`.
- **Table density** – opacity fade (no transform) over 120ms; highlight suppressed when reduced motion is active.
- **Inline edit** – pulse highlight and undo capsule within 5s window, respecting `motion-runtime` freeze flags.
- **Toolbar filter** – opacity + 2px vertical slide for badges. Busy state is capped at 250ms debounced application.

All modules check `motion-runtime` to disable animation and mark `.is-frozen` when overlays are active.

## Runtime Storage Keys
- `ui:theme` – current color tone (`soft-indigo` or `industrial-gray`).
- `ui:motion` – motion profile (`soft` or `reduced`).
- `ui:sidebar` – layout mode (`expanded` or `compact`).
- `ui:table:density:{route}:{tableId}` – compact/comfortable table preference per route + table id.

## Table Recipe (v2)
- **Storage Keys**
- Density: `ui:table:density:{route}:{tableId}`
- **Column Guidance**
  - `id` / `code`: narrow, optional left pin.
  - `description`: flexible width, two-line clamp.
  - `numeric`: right aligned, totals align with sticky footer.
  - `actions`: right pin, 56–72px guard.
- **Sticky Strategy**
  - `thead`, pinned columns, and totals row use sticky positioning with subtle separators. Totals stick only when the sentinel reports overflow.
- **Accessibility**
  - `role="grid"` with roving tabindex, `aria-colindex/rowindex`, keyboard navigation for arrows/home/end/page keys.
  - Assistive text via `.ui-table__assistive` and `aria-describedby`.
- **Search**
  - Toolbar search posts a simple GET `?q=` request; without JS the form refreshes the page, with JS it marks the table `aria-busy` until the navigation completes.
  - Controllers read the `q` parameter, pass it to a `scopeSearch` (or dedicated filter object) on the Model, and always return paginated collections with totals calculated server-side.
  - The searchable whitelist is explicit (e.g. code, name, category). For large datasets, expose extra filters such as date ranges or status chips instead of widening the free-text field.
- **Row actions**
  - At most two quiet buttons stay inline; additional options collapse into the kebab menu that opens as a `role="menu"`.
  - Destructive actions route through `<x-ui.confirm>` so ESC/backdrop dismiss consistently restores focus to the originating button.
- **Persistence**
  - Density toggles persist lazily and emit `ui:table:density` for observers.

## Overlay Contract
- **Event Bus**
  - Open/Close triggers: `ui:overlay:open`, `ui:overlay:close` (start of animation).
  - Completion: `ui:overlay:opened`, `ui:overlay:closed`.
  - Drawer/modal controllers mark overlays in `overlay-registry` to keep ESC focus traps reliable.
- **Hidden State**
  - Overlays default to `hidden` + `inert` until activated, preventing stray portal roots from swallowing pointer events.
  - Controllers remove the attributes on open and restore them once transitions finish, so reduced-motion users still see instant toggles.
- **Scroll Lock & Freeze**
  - `lockScroll` accounts for scrollbar width and toggles `is-scroll-locked` on `<body>`.
  - `motion-runtime` listens for `ui:overlay:open|close|closed` to toggle `.is-overlay-open` on `<html>` and freeze any `[data-ui='table']` or `[data-motion='list']` surfaces.
## PR Quality Gates
- Check bundle sizes against budgets: Core CSS ≤ 120 KB gzip, core JS ≤ 180 KB gzip.
- Run linting/formatting (Stylelint, ESLint, Prettier) and include results in the PR template checklist.
- Capture UI Gallery snapshots for both themes, sidebar variants, and motion modes when visual changes occur.
- Confirm accessibility checklist items (focus, aria labels, reduced motion) before merge.
