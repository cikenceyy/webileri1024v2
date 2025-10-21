# Deprecations & Cleanup Plan

| Legacy Surface | Bridge Mechanism | Removal Target | Notes |
| --- | --- | --- | --- |
| `view('crmsales::…')` & `view('crm::…')` calls | `MarketingServiceProvider` registers both namespaces to the new marketing views.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L70-L105】 | Q3 2025 | Replace includes with `marketing::` once downstream packages update. |
| `config('crmsales.crm.*')` lookups | Provider mirrors `marketing.module` config under `crmsales.crm` for backwards compatibility.【F:app/Modules/Marketing/Providers/MarketingServiceProvider.php†L95-L102】 | After config audit | Search for `config('crmsales` and switch to `marketing.module.*`. |
| `route('admin.crmsales.*')` helpers | Routing keeps alias via `config/modules.php` but new names live under `admin.marketing.*`.【F:config/modules.php†L1-L8】【F:app/Modules/Marketing/Routes/admin.php†L6-L52】 | Sprint following Marketing QA | Update Blade/templates/tests to new route names; keep alias until all consumers migrate. |
| Global JS entry `resources/js/modules/crm-sales.js` | Logic moved into `app/Modules/Marketing/Resources/js/marketing.js` with dual data attributes. | Immediate | Remove stale imports; `admin-runtime.js` already cleaned. |
| SCSS partial `modules/_crm-sales.scss` | Styles merged into module-scoped `marketing.scss` with compatibility selectors.【F:app/Modules/Marketing/Resources/scss/marketing.scss†L1-L36】 | Immediate | Delete old partial from main bundle (done in this PR). |

> Track removals via `rg 'crmsales'` to ensure no hidden usages remain before flipping aliases off.
