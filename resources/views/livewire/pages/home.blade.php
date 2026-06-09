<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950">
        <div class="absolute inset-0 opacity-20"
             style="background-image: radial-gradient(circle at 20% 30%, white 1px, transparent 1px); background-size: 28px 28px;"></div>
        <div class="container-app relative py-20 sm:py-28">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    {{ __('messages.home.hero_title') }}
                </h1>
                <p class="mt-5 text-lg text-brand-100">
                    {{ __('messages.home.hero_subtitle') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('courses.index') }}" class="btn-accent px-6 py-3 text-base">
                        {{ __('messages.home.browse_courses') }}
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn px-6 py-3 text-base bg-white/10 text-white ring-1 ring-white/30 hover:bg-white/20">
                            {{ __('messages.nav.register') }}
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Featured --}}
    <section class="container-app py-14">
        <div class="mb-8 flex items-end justify-between">
            <h2 class="text-2xl font-extrabold text-slate-900">{{ __('messages.home.featured') }}</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-bold text-brand-700 hover:underline">
                {{ __('messages.home.view_all') }} →
            </a>
        </div>

        @if ($featured->isEmpty())
            <p class="text-slate-500">{{ __('messages.common.no_results') }}</p>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        @endif
    </section>
</div>
