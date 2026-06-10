{{-- Custom modern admin theme: gradient sidebar + refined layout.
     Injected via render hook so it needs no separate Vite theme build. --}}
<style>
    :root {
        --sidebar-width: 17rem;
    }

    /* ---- App canvas --------------------------------------------------- */
    .fi-body {
        background-color: #f1f5f9;
        background-image:
            radial-gradient(60rem 60rem at 110% -10%, rgba(99, 102, 241, 0.10), transparent 60%),
            radial-gradient(50rem 50rem at -10% 110%, rgba(79, 70, 229, 0.08), transparent 55%);
        background-attachment: fixed;
    }
    .dark .fi-body {
        background-color: #0b1020;
        background-image:
            radial-gradient(60rem 60rem at 110% -10%, rgba(99, 102, 241, 0.18), transparent 60%),
            radial-gradient(50rem 50rem at -10% 110%, rgba(49, 46, 129, 0.22), transparent 55%);
    }

    /* ---- Sidebar shell ------------------------------------------------ */
    .fi-sidebar,
    .fi-sidebar.lg\:bg-transparent {
        background: linear-gradient(195deg, #312e81 0%, #29257a 45%, #1e1b4b 100%) !important;
        box-shadow: 1px 0 0 0 rgba(255, 255, 255, 0.04), 18px 0 40px -24px rgba(30, 27, 75, 0.65);
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
        --tw-ring-color: rgba(255, 255, 255, 0.08) !important;
        height: 4.5rem;
    }
    .fi-sidebar-header .fi-logo {
        color: #fff !important;
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    /* ---- Nav groups --------------------------------------------------- */
    .fi-sidebar-nav { padding: 0.5rem 0.75rem 1rem; gap: 0.25rem; }
    .fi-sidebar-group-label {
        color: #a5b4fc !important;
        text-transform: uppercase;
        font-size: 0.6875rem !important;
        letter-spacing: 0.08em;
        font-weight: 700 !important;
        padding-inline: 0.5rem;
    }

    /* ---- Nav items ---------------------------------------------------- */
    .fi-sidebar-item-button {
        border-radius: 0.75rem !important;
        padding: 0.6rem 0.7rem !important;
        color: #c7d2fe !important;
        font-weight: 600;
        position: relative;
        transition: background-color .15s ease, color .15s ease, transform .15s ease;
    }
    .fi-sidebar-item-button:hover,
    .fi-sidebar-item-button:focus-visible {
        background-color: rgba(255, 255, 255, 0.08) !important;
        color: #fff !important;
    }
    .fi-sidebar-item-label { color: inherit !important; font-weight: 600; }
    .fi-sidebar-item-icon { color: #a5b4fc !important; }

    /* Active item — glassy pill with accent marker */
    .fi-sidebar-item-active .fi-sidebar-item-button {
        background: linear-gradient(90deg, rgba(255,255,255,0.16), rgba(255,255,255,0.08)) !important;
        color: #fff !important;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
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
        background: #fbbf24;
        box-shadow: 0 0 12px rgba(251, 191, 36, 0.7);
    }
    .fi-sidebar-item-active .fi-sidebar-item-label { color: #fff !important; }
    .fi-sidebar-item-active .fi-sidebar-item-icon { color: #fff !important; }

    /* Grouped tree connector lines on the dark bg */
    .fi-sidebar-item-grouped-border .bg-gray-300 { background-color: rgba(255,255,255,0.18) !important; }

    /* Collapse / sidebar toggle buttons inside the dark panel */
    .fi-sidebar-header .fi-icon-btn { color: #c7d2fe !important; }

    /* Sidebar scrollbar */
    .fi-sidebar-nav-groups::-webkit-scrollbar { width: 6px; }
    .fi-sidebar-nav-groups::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 9999px;
    }

    /* ---- Topbar ------------------------------------------------------- */
    .fi-topbar > nav {
        background: rgba(255, 255, 255, 0.75) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 1px 0 0 rgba(15, 23, 42, 0.06), 0 10px 30px -22px rgba(15, 23, 42, 0.35);
        border-radius: 0 0 1rem 1rem;
    }
    .dark .fi-topbar > nav {
        background: rgba(17, 24, 39, 0.72) !important;
        box-shadow: 0 1px 0 0 rgba(255, 255, 255, 0.06);
    }

    /* ---- Content cards / widgets -------------------------------------- */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        border-radius: 1rem !important;
    }
    .fi-wi-stats-overview-stat {
        box-shadow: 0 1px 3px rgba(17, 24, 39, 0.06), 0 12px 28px -20px rgba(49, 46, 129, 0.35) !important;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px -16px rgba(49, 46, 129, 0.45) !important;
    }

    /* Page heading a touch tighter/bolder */
    .fi-header-heading { font-weight: 800; letter-spacing: -0.01em; }

    /* Keep file-upload dropzones compact so they don't dominate modals */
    .fi-fo-file-upload .filepond--root,
    .fi-fo-file-upload .filepond--drop-label { min-height: 4.5rem !important; }
    .fi-fo-file-upload .filepond--panel-root { border-radius: 0.75rem; }
</style>
