# Local Development

## Requirements
- PHP 8.2+
- Composer 2
- Node 18+ (build-time only)
- SQLite (default) or MySQL

## First-time setup
```bash
composer install
npm install
cp .env.example .env        # if not already present
php artisan key:generate
php artisan migrate --seed   # creates schema + demo data + admin user
npm run build                # or: npm run dev  (hot reload while developing)
```

## Run
Two terminals (or use `composer run dev` if defined):
```bash
php artisan serve            # http://127.0.0.1:8000
npm run dev                  # Vite dev server for hot asset reload
```

- Public site: http://127.0.0.1:8000
- Admin panel (Filament): http://127.0.0.1:8000/admin

## Default admin (from seeder)
- Email: `admin@example.com`
- Password: `password`
(Change these in `database/seeders/DatabaseSeeder.php` / before production.)

## Switching to MySQL locally
Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=courses
DB_USERNAME=root
DB_PASSWORD=
```
Then `php artisan migrate:fresh --seed`.

## Project conventions
- Business logic → `app/Actions` and `app/Services` (thin controllers/components).
- Validation → Form Requests. Authorization → Policies + the `can-watch-lesson` gate.
- Type-safe constants → `app/Enums`.
- No hardcoded UI strings → `lang/ar` + `lang/en`, use `__('...')`.
- Tailwind: prefer logical properties (`ms-/me-/ps-/pe-`) for RTL/LTR parity.

## Useful commands
```bash
php artisan make:filament-resource Course --generate
php artisan migrate:fresh --seed
php artisan tinker
./vendor/bin/pint            # code style (if installed)
```
