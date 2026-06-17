@php
    $locale = app()->getLocale();
    $dir = config("localization.supported.$locale.dir", 'ltr');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    {{-- Arabic + Latin web font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <x-site-colors />
</head>
<body class="min-h-screen flex flex-col font-sans">
    <x-storefront.navbar />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    {{-- Floating WhatsApp Button --}}
    <style>
        .wa-btn-wrap { position: fixed; bottom: 28px; right: 28px; z-index: 9999; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
        .wa-btn {
            position: relative;
            display: flex; align-items: center; justify-content: center;
            width: 60px; height: 60px; border-radius: 50%;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            box-shadow: 0 8px 32px rgba(37,211,102,.45), 0 2px 8px rgba(0,0,0,.18);
            text-decoration: none;
            transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s ease;
        }
        .wa-btn:hover { transform: scale(1.12); box-shadow: 0 12px 40px rgba(37,211,102,.6), 0 4px 12px rgba(0,0,0,.2); }
        .wa-btn svg { width: 32px; height: 32px; fill: #fff; }
        /* pulse ring */
        .wa-btn::before {
            content: '';
            position: absolute; inset: -6px; border-radius: 50%;
            border: 3px solid rgba(37,211,102,.5);
            animation: wa-pulse 2s ease-out infinite;
        }
        @keyframes wa-pulse {
            0%   { transform: scale(1);   opacity: .8; }
            70%  { transform: scale(1.35); opacity: 0; }
            100% { transform: scale(1.35); opacity: 0; }
        }
        /* tooltip */
        .wa-tooltip {
            background: #fff;
            color: #128C7E;
            font-size: 13px; font-weight: 600;
            padding: 6px 14px; border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,.12);
            white-space: nowrap;
            opacity: 0; transform: translateX(8px);
            transition: opacity .2s ease, transform .2s ease;
            pointer-events: none;
        }
        .wa-btn-wrap:hover .wa-tooltip { opacity: 1; transform: translateX(0); }
    </style>

    <div class="wa-btn-wrap">
        <span class="wa-tooltip">Chat with us</span>
        <a href="https://wa.me/201033548229"
           target="_blank"
           rel="noopener noreferrer"
           class="wa-btn"
           aria-label="Chat on WhatsApp">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                <path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.474 2.027 7.776L0 32l8.437-2.007A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm0 29.333a13.27 13.27 0 01-6.756-1.843l-.484-.287-5.009 1.192 1.264-4.876-.317-.501A13.27 13.27 0 012.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.27-9.862c-.398-.199-2.355-1.162-2.72-1.294-.366-.133-.632-.199-.898.199-.266.398-1.03 1.294-1.263 1.56-.233.266-.465.299-.863.1-.398-.199-1.681-.62-3.203-1.977-1.184-1.057-1.983-2.362-2.215-2.76-.233-.398-.025-.613.175-.811.18-.179.398-.465.598-.698.199-.233.266-.398.398-.664.133-.266.066-.498-.033-.698-.1-.199-.898-2.166-1.23-2.964-.324-.778-.654-.673-.898-.685l-.765-.013c-.266 0-.698.1-1.064.498-.366.398-1.396 1.363-1.396 3.323s1.429 3.853 1.628 4.12c.199.266 2.813 4.294 6.818 6.024.953.411 1.697.657 2.277.841.957.304 1.828.261 2.517.158.767-.114 2.355-.963 2.688-1.893.333-.93.333-1.728.233-1.893-.1-.165-.366-.265-.764-.464z"/>
            </svg>
        </a>
    </div>

    @livewireScripts
</body>
</html>
