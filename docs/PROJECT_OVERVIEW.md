# Project Overview — Course Selling Platform

## 1. Purpose

A bilingual (Arabic-first, RTL) course-selling platform where an admin uploads
video courses and students browse and watch them. Courses are either **free**
(watch after a simple login) or **paid** (unlocked with an **access code** that
the admin generates and hands out to the student). Course videos come from two
sources: **Bunny Stream** (premium, protected) and **YouTube** (unlisted embeds).

The platform must run on **standard Hostinger shared hosting**, which has **no
Node.js runtime** — so the entire production stack is pure PHP. JavaScript assets
are compiled locally/once at build time and uploaded as static files.

## 2. Primary User Stories

### Student
- Browse a catalog of courses; search and filter (free/paid, category).
- Open a course detail page and watch **preview lessons** for free before deciding.
- Enroll in a **free** course instantly (after login).
- Unlock a **paid** course by entering an **access code** given by the admin.
- Watch course videos (Bunny or YouTube), navigate lessons, resume where they left off.
- See "My Courses" and continue learning.

### Admin
- Create/edit courses, categories, and lessons.
- For each lesson, choose the video source (Bunny or YouTube) and mark it as a
  free **preview** or not.
- **Generate batches of access codes** for a specific course (with optional
  expiry), export them as CSV, and hand them to students.
- Track which codes are used/unused, by whom, and when; revoke unused codes.
- See enrollments and basic reporting.

## 3. Feature Summary

| Area | Features |
|------|----------|
| Catalog | Listing, search, category & free/paid filters, pagination |
| Course detail | Description, curriculum, instructor, preview lessons, enroll/unlock CTA |
| Free access | Instant enrollment after login |
| Paid access | Single-use access codes, optional expiry, secure redemption |
| Video | Bunny Stream (signed short-lived URLs) + YouTube (unlisted embeds) |
| Watch | Lesson navigation, mark-complete, resume (saved position) |
| Student | Dashboard, My Courses, profile |
| Admin | Filament panel: courses, lessons, categories, codes, enrollments, reports |
| i18n | Arabic (default, RTL) + English (LTR), full localization |
| Design | Premium, fully responsive, mobile-first, RTL-first |

## 4. Out of Scope (for v1) — but designed for

These are intentionally deferred, with the architecture left open so they slot in
without a rewrite:

- **Online payments** (Stripe / PayPal / local gateway) — would auto-generate a
  code or create an enrollment on successful purchase.
- Certificates, quizzes, coupons, reviews/ratings, multi-instructor management.

## 5. High-Level Flows

### 5.1 Access-code unlock
1. Admin generates N single-use codes tied to a specific course (optional expiry).
2. Codes are stored **hashed**; status = `unused`.
3. Student opens a paid course → enters a code.
4. Backend validates inside a DB transaction with a row lock: code exists, matches
   the course, is `unused`, and not expired → marks it `redeemed` by that student
   → creates an `enrollment`.
5. Student now has permanent access to that course's videos.

### 5.2 Secure video playback
- **Bunny:** the server stores only the video ID. On the watch page it verifies
  enrollment, then mints a **short-lived signed URL** via Bunny token
  authentication. Raw URLs never reach the browser. This is what protects paid
  content from being shared.
- **YouTube:** unlisted videos embedded; the page is gated by enrollment. Weaker
  protection — used for free/preview content; premium material should use Bunny.

## 6. Where to read more
- Architecture & technology decisions: [DECISIONS.md](DECISIONS.md)
- Database schema: [DATA_MODEL.md](DATA_MODEL.md)
- Local development: [DEVELOPMENT.md](DEVELOPMENT.md)
- Deploying to Hostinger (no Node): [DEPLOYMENT.md](DEPLOYMENT.md)
