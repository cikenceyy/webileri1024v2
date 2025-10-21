# UI Tokens Reference

## Color Tokens

| Token | Value | Usage |
| --- | --- | --- |
| `$ui-indigo` | `#4f46e5` | Primary accent; mapped to Bootstrap `$primary`. |
| `$ui-blue` | `#2563eb` | Secondary/links; mapped to `$secondary`. |
| `$ui-orange` | `#fb923c` | Warning/accent surfaces; mapped to `$warning`. |
| `$ui-gray-900` | `#111827` | Body text or dark surfaces. |
| `$ui-gray-700` | `#374151` | Muted headings/labels. |
| `$ui-gray-100` | `#f3f4f6` | Subtle backgrounds and separators. |

CSS custom properties derived from these tokens are declared at the end of `resources/scss/admin.scss` so they are emitted **after** Bootstrap. Example:

```scss
:root {
  --ui-color-primary: #{$ui-indigo};
  --ui-color-text: #1f2430;
  --ui-color-shadow: rgba(15, 23, 42, 0.12);
}
```

## Spacing Scale

| Token | Value | Notes |
| --- | --- | --- |
| `$space-0` | `0` | Reset spacing. |
| `$space-1` | `4px` | Dense padding/margins. |
| `$space-2` | `8px` | Default gap inside compact controls. |
| `$space-3` | `12px` | Card gutters. |
| `$space-4` | `16px` | Primary layout padding. |
| `$space-5` | `24px` | Section spacing / `main` padding. |
| `$space-6` | `32px` | Hero spacing. |
| `$space-7` | `40px` | Large sections. |
| `$space-8` | `48px` | Feature headers. |

CSS variables such as `--ui-space-3` are exported in `admin.scss` and `legacy/_admin.scss` continues to consume them via existing utility classes.

## Radius Tokens

| Token | Value |
| --- | --- |
| `$radius-sm` | `6px` |
| `$radius-md` | `10px` |
| `$radius-lg` | `16px` |

Example usage:

```scss
.card {
  border-radius: var(--ui-radius-md);
}
```

## Typography Tokens

| Token | Value | Notes |
| --- | --- | --- |
| `$font-family-base` | `'Inter', 'Roboto', system-ui, -apple-system, sans-serif` | Shared across admin/public entries via CSS variable `--ui-font-family`. |
| `$font-size-base` | `0.95rem` | Slightly smaller base for dense admin tables. |
| `$line-height-base` | `1.5` | Default line-height for readability. |

These tokens are consumed by both `admin.scss` and `app.scss`, enabling consistent typography between admin consoles and public pages without re-importing Bootstrap.

## Implementation Checklist

- Always import tokens **before** Bootstrap in Sass (`@use 'tokens/colors'`).
- Declare CSS custom properties **after** the Bootstrap imports to avoid `to-rgb()` / `var()` conflicts.
- When authoring module styles, read Sass tokens via `@use 'tokens/colors' as *;` and expose CSS variables with literal values (no nested `var()` in Sass assignments).
