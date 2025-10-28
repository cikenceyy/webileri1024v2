# Webileri × Laravel Cloud Patch

This patch aligns Vite output with Laravel's default manifest path (`public/build/manifest.json`), and provides Cloud-ready env templates and a sample `laravel-cloud.yml`.

## Files
- `vite.config.js` → removed custom `buildDirectory`, moved SCSS preprocessor options to top-level.
- `package.json` → ensured `dev`/`build` scripts, added `preview`.
- `.env.example` → local development (MySQL, file/session/database queue).
- `.env.staging.example` → Cloud staging template (Redis-backed cache/session/queue, S3/R2 storage).
- `.env.production.example` → Cloud production template (same as staging, hardened logging).
- `laravel-cloud.yml` → build/deploy/processes/scheduled task hints for Cloud.

## Cloud Build / Deploy
**Build**
```
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Deploy**
```
php artisan migrate --force
```

> Do **not** add `storage:link`, `queue:restart`, `optimize:clear` into Deploy. Cloud manages lifecycle and these either don't persist or are unnecessary.

## Blade
Use `@vite([...])` with entry files that exist in `vite.config.js`. All entries will emit into `public/build`, and the server will look up `public/build/manifest.json`.
