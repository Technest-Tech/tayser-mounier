# Tayser Mounier — Course Selling Platform

A bilingual (Arabic-first, RTL) platform for selling and delivering video courses.
Students browse courses, watch free preview lessons, enroll in free courses, or
unlock paid courses with admin-issued **access codes**. Videos are served from
**Bunny Stream** (protected) and **YouTube** (unlisted). Built to run on standard
**Hostinger shared hosting — no Node.js in production.**

## Stack
- **Laravel 11** (MVC, PHP 8.2+)
- **Livewire 3 + Alpine.js + Blade + Tailwind CSS** (modern reactive UI, pure-PHP runtime)
- **Filament 3** admin panel
- **MySQL** (prod) / SQLite (local dev)
- **Bunny Stream** + YouTube for video

> Node.js is used **only locally at build time** to compile assets. The server
> serves pre-compiled static files and runs no JavaScript. See
> [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Documentation
- [Project Overview](docs/PROJECT_OVERVIEW.md) — purpose, user stories, features, flows
- [Decisions](docs/DECISIONS.md) — architecture & technology choices and why
- [Data Model](docs/DATA_MODEL.md) — database schema, relationships, enums
- [Development](docs/DEVELOPMENT.md) — local setup and conventions
- [Deployment](docs/DEPLOYMENT.md) — Hostinger (no Node.js) deploy guide

## Quick start
```bash
composer install
npm install
php artisan migrate --seed
npm run dev        # and, in another terminal:
php artisan serve
```
- Public site: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin  (`admin@example.com` / `password`)
