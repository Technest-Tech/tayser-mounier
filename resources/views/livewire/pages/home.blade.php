<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950">
        {{-- decorative dotted backdrop + glow blobs --}}
        <div class="absolute inset-0 bg-dots opacity-60"></div>
        <div class="pointer-events-none absolute -top-24 end-[-6rem] h-80 w-80 rounded-full bg-accent-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-[-8rem] start-[-4rem] h-96 w-96 rounded-full bg-brand-400/20 blur-3xl"></div>

        <div class="container-app relative py-20 sm:py-28">
            <div class="max-w-2xl">
                <span class="eyebrow">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                    {{ \App\Models\Setting::get('hero_eyebrow', __('messages.home.eyebrow')) }}
                </span>

                <h1 class="mt-6 text-4xl font-extrabold leading-[1.15] text-white sm:text-5xl">
                    {{ \App\Models\Setting::get('hero_title', __('messages.home.hero_title')) }}
                </h1>
                <p class="mt-5 text-lg leading-relaxed text-brand-100">
                    {{ \App\Models\Setting::get('hero_subtitle', __('messages.home.hero_subtitle')) }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('courses.index') }}" class="btn-accent px-6 py-3 text-base shadow-lg shadow-accent-600/30">
                        {{ \App\Models\Setting::get('hero_button_text', __('messages.home.browse_courses')) }}
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn px-6 py-3 text-base bg-white/10 text-white ring-1 ring-white/30 backdrop-blur hover:bg-white/20">
                            {{ __('messages.nav.register') }}
                        </a>
                    @endguest
                </div>
            </div>

            {{-- Stats --}}
            <dl class="mt-14 grid max-w-2xl grid-cols-3 gap-px overflow-hidden rounded-2xl bg-white/10 ring-1 ring-white/15">
                @foreach ([
                    ['value' => $coursesCount, 'label' => __('messages.home.stats.courses')],
                    ['value' => $lessonsCount, 'label' => __('messages.home.stats.lessons')],
                    ['value' => $studentsCount, 'label' => __('messages.home.stats.students')],
                ] as $stat)
                    <div class="bg-white/5 px-4 py-5 text-center backdrop-blur">
                        <dt class="text-3xl font-extrabold text-white sm:text-4xl">
                            {{ number_format($stat['value']) }}<span class="text-accent-400">+</span>
                        </dt>
                        <dd class="mt-1 text-xs font-semibold text-brand-200 sm:text-sm">{{ $stat['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Why us --}}
    <section class="container-app py-16">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('messages.home.why_title') }}</h2>
            <p class="mt-3 text-slate-500">{{ __('messages.home.why_subtitle') }}</p>
        </div>

        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Simple --}}
            <div class="feature-card">
                <span class="feature-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                </span>
                <h3 class="font-extrabold text-slate-900">{{ __('messages.home.features.simple_title') }}</h3>
                <p class="text-sm leading-relaxed text-slate-500">{{ __('messages.home.features.simple_text') }}</p>
            </div>

            {{-- Anytime --}}
            <div class="feature-card">
                <span class="feature-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                </span>
                <h3 class="font-extrabold text-slate-900">{{ __('messages.home.features.anytime_title') }}</h3>
                <p class="text-sm leading-relaxed text-slate-500">{{ __('messages.home.features.anytime_text') }}</p>
            </div>

            {{-- Track --}}
            <div class="feature-card">
                <span class="feature-icon">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <h3 class="font-extrabold text-slate-900">{{ __('messages.home.features.track_title') }}</h3>
                <p class="text-sm leading-relaxed text-slate-500">{{ __('messages.home.features.track_text') }}</p>
            </div>
        </div>
    </section>

    {{-- Featured --}}
    <section class="container-app pb-16">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-2xl font-extrabold text-slate-900">{{ __('messages.home.featured') }}</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-bold text-brand-700 hover:underline">
                {{ __('messages.home.view_all') }} →
            </a>
        </div>

        @if ($featured->isEmpty())
            <div class="card grid place-items-center p-16 text-center">
                <p class="text-slate-500">{{ __('messages.common.no_results') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Featured books --}}
    <section class="container-app pb-16">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-2xl font-extrabold text-slate-900">{{ __('messages.home.featured_books') }}</h2>
            <a href="{{ route('books.index') }}" class="text-sm font-bold text-brand-700 hover:underline">
                {{ __('messages.home.view_all') }} →
            </a>
        </div>

        @if ($featuredBooks->isEmpty())
            <div class="card grid place-items-center p-16 text-center">
                <p class="text-slate-500">{{ __('books.no_results') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($featuredBooks as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- CTA band --}}
    <section class="container-app pb-20">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 to-brand-950 px-8 py-12 text-center sm:px-12 sm:py-16">
            <div class="absolute inset-0 bg-dots opacity-50"></div>
            <div class="pointer-events-none absolute -top-16 start-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-accent-500/20 blur-3xl"></div>
            <div class="relative mx-auto max-w-xl">
                <h2 class="text-2xl font-extrabold text-white sm:text-3xl">{{ __('messages.home.cta_title') }}</h2>
                <p class="mt-3 text-brand-100">{{ __('messages.home.cta_subtitle') }}</p>
                <a href="{{ route('courses.index') }}" class="btn-accent mt-7 px-7 py-3 text-base shadow-lg shadow-accent-600/30">
                    {{ __('messages.home.cta_button') }}
                </a>
            </div>
        </div>
    </section>
</div>
