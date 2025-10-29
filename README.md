# Cloud Build Fix

## Why your build failed
The error
```
Command "install
" is not defined.
Did you mean this?
    install
```
means Composer received a subcommand containing a hidden newline/CR (e.g. `install\r`/`install\n`) or stray quotes.
This happens when build commands in Cloud are copied from a Windows editor, or when YAML lines were quoted and
committed with CRLF.

## Fix in Laravel Cloud (UI)
Environment → Deployments → Build commands, **retype** (no quotes, each on its own line):
```
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
Deploy commands (only this):
```
php artisan migrate --force
```

Then redeploy.

## Repo-side hardening
1) Add `.gitattributes` from this pack to enforce **LF** endings.
2) If you keep `laravel-cloud.yml` in the repo, use the one here (no quotes, LF).

## Quick verification (Cloud → Commands)
```
composer --version
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci && npm run build
test -f public/build/manifest.json && echo "manifest OK"
php artisan about
```
Everything above should succeed, and the site should stop throwing 500 on asset lookup.
