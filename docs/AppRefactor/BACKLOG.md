# Backlog

- Legacy Blade templates under `resources/views/core/**` still contain hard-coded routes for the deprecated admin console endpoints. Replace them with the new orchestration-driven views or move them into `resources/views/legacy` once the remaining screens are migrated.
- Evaluate whether the informational redirects for `admin.consoles.*` POST routes can be replaced with full orchestration delegates once the legacy UI is retired.
- After dependencies are available, re-run `php artisan route:list` and update QA docs with the definitive snapshot for `/consoles/*` endpoints.
