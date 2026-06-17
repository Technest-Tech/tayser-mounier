@php
    $primary   = \App\Models\Setting::get('primary_color', '#4f46e5');
    $secondary = \App\Models\Setting::get('secondary_color', '#f59e0b');
@endphp
<style>
    /* ════════════════════════════════════════════════════════════════
       Dynamic site colours — injected from admin settings.
       All Tailwind brand/accent utility classes are overridden here
       so changing the DB values reflects everywhere instantly.
    ════════════════════════════════════════════════════════════════ */
    :root {
        --brand:  {{ $primary }};
        --accent: {{ $secondary }};

        /* Full brand shade scale derived via color-mix() */
        --brand-50:  color-mix(in srgb, var(--brand)  5%, white);
        --brand-100: color-mix(in srgb, var(--brand) 12%, white);
        --brand-200: color-mix(in srgb, var(--brand) 25%, white);
        --brand-300: color-mix(in srgb, var(--brand) 40%, white);
        --brand-400: color-mix(in srgb, var(--brand) 60%, white);
        --brand-500: color-mix(in srgb, var(--brand) 83%, white);
        --brand-600: var(--brand);
        --brand-700: color-mix(in srgb, var(--brand) 82%, black);
        --brand-800: color-mix(in srgb, var(--brand) 65%, black);
        --brand-900: color-mix(in srgb, var(--brand) 50%, black);
        --brand-950: color-mix(in srgb, var(--brand) 30%, black);

        /* Accent shade scale */
        --accent-400: color-mix(in srgb, var(--accent) 65%, white);
        --accent-500: var(--accent);
        --accent-600: color-mix(in srgb, var(--accent) 82%, black);
    }

    /* ── Background colours ─────────────────────────────────────── */
    .bg-brand-50  { background-color: var(--brand-50); }
    .bg-brand-100 { background-color: var(--brand-100); }
    .bg-brand-200 { background-color: var(--brand-200); }
    .bg-brand-400 { background-color: var(--brand-400); }
    .bg-brand-500 { background-color: var(--brand-500); }
    .bg-brand-600 { background-color: var(--brand-600); }
    .bg-brand-700 { background-color: var(--brand-700); }
    .bg-brand-800 { background-color: var(--brand-800); }
    .bg-brand-900 { background-color: var(--brand-900); }
    .bg-brand-950 { background-color: var(--brand-950); }

    .bg-accent-400 { background-color: var(--accent-400); }
    .bg-accent-500 { background-color: var(--accent-500); }
    .bg-accent-600 { background-color: var(--accent-600); }

    /* Opacity/transparency variants */
    .bg-brand-400\/20  { background-color: color-mix(in srgb, var(--brand-400)  20%, transparent); }
    .bg-accent-500\/20 { background-color: color-mix(in srgb, var(--accent-500) 20%, transparent); }

    /* ── Text colours ───────────────────────────────────────────── */
    .text-brand-100 { color: var(--brand-100); }
    .text-brand-200 { color: var(--brand-200); }
    .text-brand-300 { color: var(--brand-300); }
    .text-brand-600 { color: var(--brand-600); }
    .text-brand-700 { color: var(--brand-700); }
    .text-accent-400 { color: var(--accent-400); }
    .text-accent-500 { color: var(--accent-500); }

    /* ── Hover text colours ─────────────────────────────────────── */
    .hover\:text-brand-700:hover { color: var(--brand-700); }
    .group:hover .group-hover\:text-brand-700 { color: var(--brand-700); }

    /* ── Hover background colours ───────────────────────────────── */
    .hover\:bg-brand-700:hover { background-color: var(--brand-700); }
    .hover\:bg-brand-50:hover  { background-color: var(--brand-50); }

    /* ── Border / ring colours ──────────────────────────────────── */
    .border-brand-500 { border-color: var(--brand-500); }
    .border-brand-600 { border-color: var(--brand-600); }
    .ring-brand-500   { --tw-ring-color: var(--brand-500); }
    .ring-brand-600   { --tw-ring-color: var(--brand-600); }
    .ring-brand-600\/20 { --tw-ring-color: color-mix(in srgb, var(--brand-600) 20%, transparent); }

    .focus\:ring-brand-500:focus   { --tw-ring-color: var(--brand-500); }
    .focus\:border-brand-500:focus { border-color: var(--brand-500); }

    /* ── Shadow colour ──────────────────────────────────────────── */
    .shadow-brand-900\/25  { --tw-shadow-color: color-mix(in srgb, var(--brand-900) 25%, transparent); }
    .shadow-accent-600\/30 { --tw-shadow-color: color-mix(in srgb, var(--accent-600) 30%, transparent); }

    /* ── Gradient from / via / to ───────────────────────────────── */
    /* Order matters: from < via < to, so via's --tw-gradient-stops wins */
    .from-brand-500 {
        --tw-gradient-from: var(--brand-500) var(--tw-gradient-from-position);
        --tw-gradient-to:   transparent var(--tw-gradient-to-position);
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
    }
    .from-brand-700 {
        --tw-gradient-from: var(--brand-700) var(--tw-gradient-from-position);
        --tw-gradient-to:   transparent var(--tw-gradient-to-position);
        --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
    }
    .via-brand-800 {
        --tw-gradient-stops: var(--tw-gradient-from), var(--brand-800) var(--tw-gradient-via-position), var(--tw-gradient-to);
    }
    .to-brand-800 { --tw-gradient-to: var(--brand-800) var(--tw-gradient-to-position); }
    .to-brand-950 { --tw-gradient-to: var(--brand-950) var(--tw-gradient-to-position); }

    /* ── Filament admin sidebar & UI ────────────────────────────── */
    .fi-body {
        background-image:
            radial-gradient(60rem 60rem at 110% -10%, color-mix(in srgb, var(--brand) 10%, transparent), transparent 60%),
            radial-gradient(50rem 50rem at -10% 110%, color-mix(in srgb, var(--brand)  8%, transparent), transparent 55%);
    }
    .dark .fi-body {
        background-image:
            radial-gradient(60rem 60rem at 110% -10%, color-mix(in srgb, var(--brand) 18%, transparent), transparent 60%),
            radial-gradient(50rem 50rem at -10% 110%, color-mix(in srgb, var(--brand) 22%, transparent), transparent 55%);
    }
    .fi-sidebar,
    .fi-sidebar.lg\:bg-transparent {
        background: linear-gradient(195deg, var(--brand-900) 0%, color-mix(in srgb, var(--brand-900) 95%, var(--brand-950)) 45%, var(--brand-950) 100%) !important;
    }
    .fi-sidebar-group-label { color: var(--brand-300) !important; }
    .fi-sidebar-item-button { color: var(--brand-200) !important; }
    .fi-sidebar-item-icon   { color: var(--brand-300) !important; }
    .fi-sidebar-item-active .fi-sidebar-item-button::before {
        background: var(--accent) !important;
        box-shadow: 0 0 12px color-mix(in srgb, var(--accent) 70%, transparent) !important;
    }
    .fi-wi-stats-overview-stat {
        box-shadow: 0 1px 3px rgba(17,24,39,.06), 0 12px 28px -20px color-mix(in srgb, var(--brand-950) 35%, transparent) !important;
    }
    .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 14px 32px -16px color-mix(in srgb, var(--brand-950) 45%, transparent) !important;
    }
</style>
