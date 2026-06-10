# Architecture & Technology Decisions

Every decision here was made deliberately for this project's constraints:
**Arabic-first bilingual**, **premium responsive UI**, **easy to maintain and
extend**, and **deployable on standard Hostinger shared hosting with no Node.js**.

## D1 — Backend: Laravel 11 (MVC)
Mature, well-documented PHP MVC framework. Runs perfectly on Hostinger shared
hosting. Provides routing, Eloquent ORM, validation, queues, localization, and
auth out of the box. PHP 8.2+ required (host has 8.2.28).

## D2 — Frontend: Livewire 3 + Alpine.js + Blade + Tailwind
**Chosen over** Inertia+Vue/React and a decoupled SPA.

- **Why:** Livewire renders on the server in pure PHP — there is **no Node.js
  runtime in production**, which is exactly Hostinger's limitation. It still gives
  a modern, reactive, component-based UX (live validation, dynamic forms, partial
  updates) without an SPA or a separate API.
- Alpine.js handles light client-side interactivity (menus, modals).
- Tailwind CSS gives a consistent, premium design system.
- **Node is used only at build time** (locally), never on the server. See
  [DEPLOYMENT.md](DEPLOYMENT.md).

## D3 — Admin panel: Filament 3
**Chosen over** hand-built Livewire admin.

- **Why:** Filament is the more maintainable and extensible option — structured
  Resources, auto-generated forms/tables, a large plugin ecosystem, RTL +
  translation support built in. It is built on Livewire, so it shares our stack.
  We spend our effort on the student experience and video security instead of
  re-building CRUD tables.

## D4 — Database: MySQL (prod) / SQLite (local dev)
MySQL is included with Hostinger and is the production database. Local development
defaults to SQLite for zero-setup runnability; switching is a `.env` change. All
migrations are written to work on both.

## D5 — Access codes: single-use, one code = one course, optional expiry
- Each code unlocks exactly **one specific course** and is **burned after one
  student redeems it** — the cleanest model for selling per-course.
- Admin generates codes in **batches**; each batch may set an **optional expiry**
  date (or leave codes permanent).
- Each code is stored two ways: a one-way **HMAC `code_hash`** (used for fast,
  secure redemption lookup) and an **encrypted, reversible `code_encrypted`**
  copy (Laravel `encrypted` cast) so the admin can later **view and re-share**
  codes. Trade-off: anyone with DB + app-key access could decrypt codes —
  acceptable for an admin-only panel and required to show codes in the UI. Codes
  are still never stored as plaintext.
- Admin codes page is **grouped one row per course** (totals: total / used /
  available) with a popup showing every code and its usage history, plus
  per-course CSV export.
- Redemption is wrapped in a **DB transaction with a row lock** so two students
  can't redeem the same code simultaneously.

## D6 — Payments: not in v1 (codes only)
Admin distributes codes offline. No payment gateway is integrated yet, but the
redemption/enrollment logic is isolated in Action classes
(`RedeemAccessCodeAction`, `GenerateCodeBatchAction`) so a future gateway can call
the same code path.

## D7 — Video: Bunny Stream (protected) + YouTube (unlisted)
- **Bunny Stream** for premium/paid content: store only the video ID; serve via
  **short-lived signed URLs** (token authentication) after an enrollment check.
- **YouTube** unlisted embeds for free/preview content; page gated by enrollment.
- A single gate/policy (`can-watch-lesson`) centralizes the rule: *preview lesson
  → anyone; otherwise → must be enrolled.*

## D8 — Localization: Arabic-first (RTL) + English (LTR)
- Default locale `ar`, fallback `en`. **No hardcoded UI strings** — everything in
  `lang/ar` and `lang/en`.
- A `SetLocale` middleware resolves the locale from session/cookie.
- The layout sets `<html dir>` dynamically; Tailwind uses **logical properties**
  (`ms-/me-/ps-/pe-`) so one stylesheet serves both directions.
- Arabic web font (Tajawal/Cairo) with a Latin fallback.

## D9 — Code organization for maintainability & extension
- **Thin controllers / Livewire components**; business logic in **Action &
  Service classes** (`app/Actions`, `app/Services`).
- **Form Requests** for validation; **Policies** for authorization.
- **Backed Enums** for `role`, `lesson source`, `code status`, `enrollment
  source` — type-safe, no magic strings.
- **Eloquent query scopes** (`Course::published()->paid()`) instead of a heavy
  repository layer.
- Feature-grouped so deferred features (payments, certificates) drop in cleanly.

## D10 — Queues & scheduling on shared hosting
- Queue driver = `database` (no Redis on shared hosting).
- A single Hostinger cron entry runs `php artisan schedule:run` every minute to
  drive scheduled tasks (code-expiry cleanup, emails). See [DEPLOYMENT.md](DEPLOYMENT.md).

## Decision log (answered during planning)
| Question | Decision |
|----------|----------|
| Frontend approach | Blade + Livewire + Alpine (D2) |
| Access code behavior | One code = one course, single-use (D5) |
| Code expiry | Optional per batch (D5) |
| Payments | Codes only for now (D6) |
| Admin panel | Filament — easiest to maintain & extend (D3) |
| Languages | Arabic (primary, RTL) + English (D8) |
| Hosting | Hostinger shared, no Node.js in prod (D2, DEPLOYMENT.md) |
