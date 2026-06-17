{{-- Dynamic colour variables — same source as the storefront --}}
<x-site-colors />

{{-- Custom modern admin theme: gradient sidebar + refined layout. --}}
<style>
    :root { --sidebar-width: 17rem; }

    /* ---- App canvas --------------------------------------------------- */
    .fi-body {
        background-color: #f1f5f9;
        background-attachment: fixed;
        /* background-image overridden by site-colors component */
    }
    .dark .fi-body { background-color: #0b1020; }

    /* ---- Sidebar shell ------------------------------------------------ */
    /* background overridden by site-colors component */
    .fi-sidebar,
    .fi-sidebar.lg\:bg-transparent {
        box-shadow: 1px 0 0 0 rgba(255,255,255,0.04), 18px 0 40px -24px color-mix(in srgb, var(--brand-950) 65%, transparent);
        border: 0 !important;
    }
    @media (min-width: 1024px) {
        .fi-sidebar {
            margin: 0.75rem 0 0.75rem 0.75rem;
            border-radius: 1.25rem;
            height: calc(100vh - 1.5rem) !important;
            overflow: hidden;
        }
        .fi-main-ctn { padding-inline-start: 0.25rem; }
    }

    /* Brand header */
    .fi-sidebar-header {
        background: transparent !important;
        box-shadow: none !important;
        --tw-ring-color: rgba(255,255,255,0.08) !important;
        height: 4.5rem;
    }
    .fi-sidebar-header .fi-logo { color: #fff !important; font-weight: 800; letter-spacing: -0.01em; }

    /* ---- Nav groups --------------------------------------------------- */
    .fi-sidebar-nav { padding: 0.5rem 0.75rem 1rem; gap: 0.25rem; }
    /* .fi-sidebar-group-label color set by site-colors component */
    .fi-sidebar-group-label {
        text-transform: uppercase;
        font-size: 0.6875rem !important;
        letter-spacing: 0.08em;
        font-weight: 700 !important;
        padding-inline: 0.5rem;
    }

    /* ---- Nav items ---------------------------------------------------- */
    /* .fi-sidebar-item-button color set by site-colors component */
    .fi-sidebar-item-button {
        border-radius: 0.75rem !important;
        padding: 0.6rem 0.7rem !important;
        font-weight: 600;
        position: relative;
        transition: background-color .15s ease, color .15s ease, transform .15s ease;
    }
    .fi-sidebar-item-button:hover,
    .fi-sidebar-item-button:focus-visible {
        background-color: rgba(255,255,255,0.08) !important;
        color: #fff !important;
    }
    .fi-sidebar-item-label { color: inherit !important; font-weight: 600; }
    /* .fi-sidebar-item-icon color set by site-colors component */

    /* Active item */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background: linear-gradient(90deg, rgba(255,255,255,0.16), rgba(255,255,255,0.08)) !important;
        color: #fff !important;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
    }
    .fi-sidebar-item-active .fi-sidebar-item-button::before {
        content: '';
        position: absolute;
        inset-inline-start: -0.35rem;
        top: 50%;
        transform: translateY(-50%);
        height: 1.25rem;
        width: 0.25rem;
        border-radius: 9999px;
        /* color + shadow set by site-colors component */
    }
    .fi-sidebar-item-active .fi-sidebar-item-label { color: #fff !important; }
    .fi-sidebar-item-active .fi-sidebar-item-icon  { color: #fff !important; }

    .fi-sidebar-item-grouped-border .bg-gray-300 { background-color: rgba(255,255,255,0.18) !important; }
    .fi-sidebar-header .fi-icon-btn { color: color-mix(in srgb, var(--brand-200) 80%, white) !important; }

    /* Scrollbar */
    .fi-sidebar-nav-groups::-webkit-scrollbar { width: 6px; }
    .fi-sidebar-nav-groups::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.18);
        border-radius: 9999px;
    }

    /* ---- Topbar ------------------------------------------------------- */
    .fi-topbar > nav {
        background: rgba(255,255,255,0.75) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 1px 0 0 rgba(15,23,42,0.06), 0 10px 30px -22px rgba(15,23,42,0.35);
        border-radius: 0 0 1rem 1rem;
    }
    .dark .fi-topbar > nav {
        background: rgba(17,24,39,0.72) !important;
        box-shadow: 0 1px 0 0 rgba(255,255,255,0.06);
    }

    /* ---- Content cards / widgets -------------------------------------- */
    .fi-section, .fi-wi-stats-overview-stat, .fi-ta-ctn {
        border-radius: 1rem !important;
    }
    /* .fi-wi-stats-overview-stat box-shadow set by site-colors component */
    .fi-wi-stats-overview-stat { transition: transform .15s ease, box-shadow .15s ease; }
    .fi-wi-stats-overview-stat:hover { transform: translateY(-2px); }

    .fi-header-heading { font-weight: 800; letter-spacing: -0.01em; }

    .fi-fo-file-upload .filepond--root,
    .fi-fo-file-upload .filepond--drop-label { min-height: 4.5rem !important; }
    .fi-fo-file-upload .filepond--panel-root { border-radius: 0.75rem; }
</style>
