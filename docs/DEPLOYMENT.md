# Deploying to Hostinger (shared hosting, NO Node.js)

The golden rule: **Node.js runs only on your local machine at build time. The
server never runs Node, npm, or Vite.** You compile assets locally, then upload
the app *including the already-compiled* `public/build/` folder.

## One-time server setup
1. **PHP version:** set the domain's PHP to **8.2+** in hPanel.
2. **Document root:** point the domain to the project's **`public/`** directory
   (not the project root). On Hostinger you can either set the web root to
   `public/` or place the app outside `public_html` and symlink.
3. **MySQL:** create a database + user in hPanel; note the host, db name, user,
   password.
4. **Cron:** add a single cron job running every minute:
   ```
   * * * * * cd /home/USER/path-to-app && php artisan schedule:run >> /dev/null 2>&1
   ```

## Build locally (the only Node step)
On your machine (Node v18+):
```bash
npm install
npm run build          # outputs compiled assets into public/build/
```
Commit/keep the `public/build/` output so it ships with the deploy.

## Deploy steps (each release)
From your machine, build first (above). Then upload the project to the server
(Git deploy, SFTP, or hPanel file manager). On the server run **PHP/Composer only**:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan filament:assets:publish        # pure PHP, publishes Filament's prebuilt assets
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

No `npm`, no `node`, no `vite` on the server — ever.

## .env on the server (key values)
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

QUEUE_CONNECTION=database
SESSION_DRIVER=database

# Bunny Stream
BUNNY_LIBRARY_ID=...
BUNNY_API_KEY=...
BUNNY_TOKEN_AUTH_KEY=...          # from the pull zone, for signed URLs
BUNNY_CDN_HOSTNAME=...            # e.g. vz-xxxx.b-cdn.net
```

## Queue worker on shared hosting
There is no long-running worker. Either:
- Keep heavy work minimal and let the scheduled `schedule:run` dispatch a short
  `queue:work --stop-when-empty` via the scheduler, **or**
- Use `QUEUE_CONNECTION=sync` for v1 if email volume is low.

## Filament assets
Filament ships **prebuilt** CSS/JS. `php artisan filament:assets:publish` copies
them into `public/` — this is pure PHP and needs no Node. Re-run it after any
Filament upgrade.

## Checklist before going live
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] `public/build/` present (built locally)
- [ ] `php artisan filament:assets:publish` run
- [ ] `storage:link` created
- [ ] config/route/view caches built
- [ ] MySQL migrated
- [ ] Cron for `schedule:run` active
- [ ] Bunny token authentication enabled in the pull zone
