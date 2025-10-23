# CMS Module Learning Summary

## Module Topology
- **Service Provider**: `app/Cms/Providers/CmsServiceProvider.php` boots the view namespace (`cms::`), publishes config, and binds helpers such as `CmsRepository`, preview storage, and media uploader.
- **Routing**
  - Admin routes live in `app/Cms/routes/admin.php` and are wrapped with the `web`, `tenant`, `auth`, and `permission:cms.manage` middleware stack.
  - Public routes sit in `app/Cms/routes/site.php`, expose bilingual endpoints (`/` and `/en/...`) plus sitemap and robots handlers.
- **Views & Assets**: All public and admin templates render from `app/Cms/Resources/views/**`. Page-scoped SCSS/JS entries are located under `app/Cms/Resources/assets/{scss,js}/site/` and compiled through Vite into `public/build/cms`.
- **Translations**: Localisable UI copy resides in `app/Cms/Resources/lang/{tr,en}` with fallback to Turkish if an English string is missing.

## Content Model
- Content is stored per **page → block → field** inside `cms_contents` via `CmsRepository`.
- Supported field types: text, multiline/textarea, image, file, link, and repeater collections.
- SEO metadata (`meta_title`, `meta_description`, `og_image`) and script snippets are saved alongside each page payload.
- Repository caching keeps a per-page/per-locale JSON blob for one hour and merges preview overlays for the live editor.

## Front of House Theme
- Layout: `site/layout.blade.php` establishes header/footer with responsive container widths and page-level asset injection via `@push('site-styles')/@push('site-scripts')`.
- Patterns & utilities: SCSS tokens (`base/_tokens.scss`) define neutral greys, primary indigo, accent orange, spacing scale (4–32), radii, and typography (14–48). Utilities provide stack spacing, flexible clusters, ratio helpers, skeleton shimmer, and responsive grids (`repeat(auto-fit, minmax(...))`).
- Common JS modules: Intersection Observer reveals, lazy media loader, skeleton clearing, beacon analytics, sticky header, map-on-demand, and client-side validation.

## Data Providers
- `ProductProvider` resolves inventory products (with feature flags, gallery media, locale slugs) or stub data when the inventory module is unavailable.
- `CatalogProvider` reads catalog repeaters from CMS content with locale fallback and stub placeholders.

## Existing Behaviour & Notes for Expansion
- Page assets already load per route; hero critical CSS is inlined on the home layout.
- Lazy loading, explicit dimensions, and skeleton placeholders keep CLS low, while beacons fire CTA interactions.
- Contact form performs honeypot, submission delay, and rate limiting before persisting messages and emailing configured recipients.
- Preview/editor pipeline offers draft overlays, upload staging, and cache invalidation + warm-up jobs.

## Improvement Opportunities Identified
- Extend content config to cover the richer storytelling blocks described in Adım 3 (industries, process steps, corporate timeline, etc.).
- Enhance SCSS with fluid typography/spacing for denser pages and add new pattern styles (timelines, stat bands, partner logo rails).
- Bolster page JS for client-side filters (product & catalog chips), gallery interactions, KVKK TOC highlighting, and additional beacon triggers.
- Update Product/Catalog providers to emit lightweight category/year metadata for filtering while preserving fallbacks.
- Surface admin guidance (field hints, image sizing, fallback notices) for the new blocks and ensure validation handles additional repeater inputs.
