@php
    use App\Models\Setting;

    $locale = app()->getLocale();
    $dir = config("localization.supported.$locale.dir", 'ltr');

    $site  = Setting::get('site_title', __('messages.app_name'));
    $brand = Setting::get('primary_color', '#4f46e5');
    $gold  = Setting::get('secondary_color', '#f59e0b');

    // hex -> rgba helper for translucent tints (broad print-engine support).
    $rgba = function (string $hex, float $a): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba($r, $g, $b, $a)";
    };

    $score = $attempt->score;
    $total = $attempt->total;
    $date  = ($attempt->completed_at ?? now())->locale($locale)->translatedFormat('d F Y');
    $verification = 'TM-'.str_pad((string) $lesson->id, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $attempt->id, 6, '0', STR_PAD_LEFT);
    $fileSlug = \Illuminate\Support\Str::slug($lesson->title) ?: 'certificate';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('certificate.page_title') }} — {{ $site }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #0f172a;
            background-image:
                radial-gradient(60rem 60rem at 110% -10%, {{ $rgba($brand, 0.25) }}, transparent 60%),
                radial-gradient(50rem 50rem at -10% 110%, {{ $rgba($gold, 0.18) }}, transparent 55%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 28px 16px 56px;
        }

        /* ── Toolbar (not part of the captured certificate) ───────────── */
        .toolbar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 12px;
            padding: 12px 24px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary {
            color: #fff;
            background: linear-gradient(135deg, {{ $brand }}, {{ $gold }});
            box-shadow: 0 12px 30px {{ $rgba($brand, 0.4) }};
        }
        .btn-primary[disabled] { opacity: .6; cursor: wait; transform: none; }
        .btn-ghost {
            color: #e2e8f0;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
        }
        .btn svg { width: 18px; height: 18px; }

        /* ── Scaling stage so the fixed-size certificate fits any screen ── */
        .stage {
            transform-origin: top center;
            transition: transform .1s ease;
        }

        /* ── The certificate itself (fixed A4 landscape @96dpi) ───────── */
        .cert {
            width: 1123px;
            height: 794px;
            padding: 16px;
            background: linear-gradient(135deg, {{ $brand }} 0%, {{ $rgba($brand, 0.85) }} 45%, {{ $gold }} 100%);
            box-shadow: 0 40px 80px rgba(0,0,0,.45);
        }
        .cert-inner {
            position: relative;
            width: 100%;
            height: 100%;
            background: #fffdf7;
            border: 2px solid {{ $gold }};
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 46px 72px 40px;
        }

        /* Watermark monogram behind the content */
        .watermark {
            position: absolute;
            top: 50%;
            {{ $dir === 'rtl' ? 'right' : 'left' }}: 50%;
            transform: translate(50%, -50%);
            font-family: 'Amiri', serif;
            font-size: 540px;
            font-weight: 700;
            line-height: 1;
            color: {{ $rgba($brand, 0.04) }};
            pointer-events: none;
            user-select: none;
            white-space: nowrap;
        }
        @if ($dir !== 'rtl')
            .watermark { transform: translate(-50%, -50%); }
        @endif

        /* Decorative gold corner brackets */
        .corner {
            position: absolute;
            width: 64px;
            height: 64px;
            border: 3px solid {{ $gold }};
        }
        .corner.tl { top: 18px; left: 18px; border-right: 0; border-bottom: 0; border-top-left-radius: 6px; }
        .corner.tr { top: 18px; right: 18px; border-left: 0; border-bottom: 0; border-top-right-radius: 6px; }
        .corner.bl { bottom: 18px; left: 18px; border-right: 0; border-top: 0; border-bottom-left-radius: 6px; }
        .corner.br { bottom: 18px; right: 18px; border-left: 0; border-top: 0; border-bottom-right-radius: 6px; }

        .content { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; width: 100%; height: 100%; }

        /* Seal / medal */
        .seal {
            width: 78px;
            height: 78px;
            border-radius: 50%;
            background: linear-gradient(135deg, {{ $gold }}, {{ $brand }});
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px {{ $rgba($gold, 0.45) }};
            margin-bottom: 14px;
            flex-shrink: 0;
        }
        .seal-ring {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 2px dashed rgba(255,255,255,.85);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 30px;
        }

        .badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: {{ $gold }};
            background: {{ $rgba($gold, 0.12) }};
            border: 1px solid {{ $rgba($gold, 0.5) }};
            border-radius: 999px;
            padding: 5px 16px;
            margin-bottom: 14px;
        }

        .heading {
            font-family: 'Amiri', serif;
            font-size: 50px;
            font-weight: 700;
            color: {{ $brand }};
            line-height: 1.1;
        }
        .site-name { margin-top: 4px; font-size: 15px; font-weight: 700; color: #64748b; }

        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 14px 0 16px;
        }
        .divider .line { width: 90px; height: 2px; background: {{ $gold }}; }
        .divider .diamond { width: 9px; height: 9px; background: {{ $gold }}; transform: rotate(45deg); }

        .certifies { font-size: 16px; color: #475569; font-weight: 500; }

        .student {
            margin: 8px 0 4px;
            font-size: 44px;
            font-weight: 900;
            color: #0f172a;
            padding-bottom: 6px;
            border-bottom: 3px solid {{ $gold }};
            display: inline-block;
            max-width: 90%;
        }

        .desc { font-size: 16px; color: #475569; font-weight: 500; margin-top: 14px; line-height: 1.7; }
        .desc strong { color: {{ $brand }}; font-weight: 800; }

        /* Stats */
        .stats {
            display: flex;
            gap: 16px;
            margin-top: 22px;
        }
        .stat {
            min-width: 132px;
            background: #fff;
            border: 1px solid {{ $rgba($brand, 0.15) }};
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 8px 22px {{ $rgba($brand, 0.07) }};
        }
        .stat .label { font-size: 12px; color: #94a3b8; font-weight: 700; letter-spacing: .5px; }
        .stat .value { margin-top: 4px; font-size: 26px; font-weight: 900; color: {{ $brand }}; }
        .stat .value.gold { color: {{ $gold }}; }

        /* Footer */
        .footer {
            margin-top: auto;
            width: 100%;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding-top: 18px;
        }
        .foot-block { text-align: center; min-width: 200px; }
        .foot-block .sig-line { width: 100%; height: 2px; background: #cbd5e1; margin-bottom: 6px; }
        .foot-block .sig-name { font-size: 15px; font-weight: 800; color: #0f172a; }
        .foot-block .sig-role { font-size: 12px; color: #94a3b8; font-weight: 600; }
        .verify { font-size: 12px; color: #94a3b8; text-align: {{ $dir === 'rtl' ? 'right' : 'left' }}; }
        .verify .vid { font-family: 'Courier New', monospace; font-weight: 700; color: #475569; letter-spacing: 1px; }

        .foot-seal {
            width: 92px;
            height: 92px;
            border-radius: 50%;
            border: 3px double {{ $gold }};
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: {{ $brand }};
            background: {{ $rgba($gold, 0.06) }};
        }
        .foot-seal .star { font-size: 24px; color: {{ $gold }}; line-height: 1; }
        .foot-seal .pct { font-size: 22px; font-weight: 900; }
        .foot-seal .pct-label { font-size: 9px; font-weight: 700; color: #94a3b8; letter-spacing: 1px; }

        @media (max-width: 480px) {
            .toolbar .btn { padding: 10px 16px; font-size: 13px; }
        }

        @if ($dir === 'rtl')
        /* letter-spacing severs Arabic cursive joins — keep it off for RTL text. */
        .badge, .stat .label, .foot-seal .pct-label { letter-spacing: 0 !important; }
        @endif

        /* ── Print / Save as PDF: native engine shapes Arabic correctly ──── */
        @media print {
            @page { size: 297mm 210mm; margin: 0; }
            html, body {
                background: #fff !important;
                margin: 0;
                padding: 0;
                display: block;
                min-height: 0;
            }
            .toolbar { display: none !important; }
            /* Undo the on-screen fit-to-viewport scaling set via JS. */
            .stage { transform: none !important; height: auto !important; }
            .cert {
                width: 297mm;
                height: 210mm;
                box-shadow: none !important;
            }
            /* Force gradients/background colours to print. */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar" id="toolbar">
        <button class="btn btn-primary" id="downloadBtn" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span id="downloadLabel">{{ __('certificate.download_pdf') }}</span>
        </button>
        <a class="btn btn-ghost" href="{{ route('courses.watch', [$course, $lesson]) }}">
            {{ __('certificate.back') }}
        </a>
    </div>

    <div class="stage" id="stage">
        <div class="cert" id="certificate">
            <div class="cert-inner">
                <div class="watermark">{{ mb_substr($site, 0, 1) }}</div>

                <span class="corner tl"></span>
                <span class="corner tr"></span>
                <span class="corner bl"></span>
                <span class="corner br"></span>

                <div class="content">
                    <div class="seal">
                        <div class="seal-ring">★</div>
                    </div>

                    <span class="badge">{{ __('certificate.badge') }}</span>

                    <h1 class="heading">{{ __('certificate.heading') }}</h1>
                    <div class="site-name">{{ $site }}</div>

                    <div class="divider">
                        <span class="line"></span>
                        <span class="diamond"></span>
                        <span class="line"></span>
                    </div>

                    <p class="certifies">{{ __('certificate.certifies', ['site' => $site]) }}</p>
                    <div class="student">{{ $user->name }}</div>

                    <p class="desc">
                        {{ __('certificate.completed_exam') }}
                        <strong>«{{ $lesson->title }}»</strong>
                        <br>
                        {{ __('certificate.within_course') }}
                        <strong>«{{ $course->title }}»</strong>
                    </p>

                    <div class="stats">
                        <div class="stat">
                            <div class="label">{{ __('certificate.score') }}</div>
                            <div class="value">{{ $score }} / {{ $total }}</div>
                        </div>
                        <div class="stat">
                            <div class="label">{{ __('certificate.percentage') }}</div>
                            <div class="value gold">{{ $percentage }}%</div>
                        </div>
                        <div class="stat">
                            <div class="label">{{ __('certificate.grade') }}</div>
                            <div class="value">{{ $grade }}</div>
                        </div>
                        <div class="stat">
                            <div class="label">{{ __('certificate.date') }}</div>
                            <div class="value" style="font-size:18px;">{{ $date }}</div>
                        </div>
                    </div>

                    <div class="footer">
                        <div class="verify">
                            {{ __('certificate.verification') }}<br>
                            <span class="vid">{{ $verification }}</span>
                        </div>

                        <div class="foot-seal">
                            <span class="star">★</span>
                            <span class="pct">{{ $percentage }}%</span>
                            <span class="pct-label">{{ __('certificate.grade') }}</span>
                        </div>

                        <div class="foot-block">
                            <div class="sig-line"></div>
                            <div class="sig-name">{{ $site }}</div>
                            <div class="sig-role">{{ __('certificate.signature') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CERT_WIDTH = 1123;
        const CERT_HEIGHT = 794;

        // Scale the fixed-size certificate down to fit the viewport.
        function fitStage() {
            const stage = document.getElementById('stage');
            const available = window.innerWidth - 32;
            const scale = Math.min(1, available / CERT_WIDTH);
            stage.style.transform = `scale(${scale})`;
            stage.style.height = (CERT_HEIGHT * scale) + 'px';
        }
        window.addEventListener('resize', fitStage);
        fitStage();

        // Make sure the web fonts are loaded before a print is triggered, so the
        // certificate is captured with Tajawal/Amiri rather than a fallback.
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.catch(() => {});
        }
    </script>
</body>
</html>
