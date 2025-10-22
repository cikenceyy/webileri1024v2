# Bluewave Theme Summary

## Core Palette
- **Primary 600**: `#1d6fe1` (base blue used for actions and highlights)
- **Primary 700**: `#1556b5` (hover/focus tone for elevated elements)
- **Accent 500**: `#4689ef` (subtle supportive blue for secondary UI)
- **Surface**: `#f4f7fc` (application background)
- **Surface Alt**: `#ffffff` (cards and elevated panels)
- **Surface Stronger**: `#e6edf8` (strips, data emphasis backgrounds)
- **Border**: `#ced7e7` (default border) · **Border Strong**: `#a9b7d0`
- **Text Primary**: `#1c2436` · **Text Strong**: `#0f172a`
- **Text Subtle**: `#475066` · **Text Muted**: `#606a80`
- **Status**: Success `#2b9c5f`, Warning `#f59e0b`, Danger `#e54848`
- **Focus Ring**: `rgba(29, 111, 225, 0.32)` (WCAG AA compliant outline)

All palette tokens live in `resources/scss/tokens/_colors.scss` and are exported both as Sass variables and CSS custom properties under `:root`.

## Token Sources
- **Spacing, Radius, Typography**: unified under `resources/scss/tokens/` with CSS variables (`--ui-space-*`, `--ui-radius-*`, `--ui-font-*`).
- **Motion & Effects**: unchanged token files now consume the new color focus ring.

## Application
- **Global**: `resources/scss/admin.scss` and `resources/scss/app.scss` now rely solely on token imports without re-declaring palettes.
- **Components**: Buttons, forms, pagination, toolbar, tables updated to reference CSS variables and new palette (no hard-coded purple/indigo RGBA).
- **Sidebar**: footer and highlights refactored to color-mix with primary; gradients removed per requirement.
- **Drive Module**: SCSS rewritten to consume tokens and present the new layout with consistent buttons, inputs, and badges.
- **UI Gallery**: Theme compare cards use `data-theme="bluewave"`; controls replaced with a static theme pill referencing the single palette.

## Removed / Deprecated
- Secondary theme variant (`data-theme="soft-indigo"`, `industrial-gray`) and runtime references.
- Gradient backgrounds across utilities (`_mixins.scss`), sidebar, drawer, tables, gallery, and drive styles.
- Legacy page-level palette overrides in `admin.scss` and `app.scss`.

## Accessibility Notes
- Hover/focus states use `color-mix` with the primary tokens to maintain WCAG AA contrast.
- Focus rings leverage `--ui-color-focus-ring`; tables and buttons receive consistent outline styling.

## Follow-up Checklist
- When adding new UI, import tokens and use CSS variables (`var(--ui-color-*)`) instead of hex codes.
- Keep button/field components (`resources/views/components/ui-*`) as the entry point for interactive elements.
- For additional status colors, extend tokens rather than introducing ad-hoc values.
