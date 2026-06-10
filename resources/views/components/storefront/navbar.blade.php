<header
    x-data="{ open: false }"
    class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur"
>
    <nav class="container-app flex h-16 items-center justify-between gap-4">
        {{-- Brand --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <span class="brand-mark h-9 w-9 text-lg">ت</span>
            <span class="text-lg font-extrabold tracking-tight text-slate-900">{{ __('messages.app_name') }}</span>
        </a>

        {{-- Desktop links --}}
        <div class="hidden items-center gap-1 md:flex">
            <a href="{{ route('home') }}" class="btn-ghost">{{ __('messages.nav.home') }}</a>
            <a href="{{ route('courses.index') }}" class="btn-ghost">{{ __('messages.nav.courses') }}</a>
            @auth
                <a href="{{ route('my-courses') }}" class="btn-ghost">{{ __('messages.nav.my_courses') }}</a>
            @endauth
        </div>

        {{-- Right side --}}
        <div class="flex items-center gap-2">
            <x-locale-switcher />

            @auth
                <div x-data="{ menu: false }" class="relative hidden md:block">
                    <button @click="menu = !menu" class="btn-outline">
                        {{ Str::limit(auth()->user()->name, 14) }}
                    </button>
                    <div
                        x-show="menu" x-cloak @click.outside="menu = false"
                        class="absolute end-0 mt-2 w-48 overflow-hidden rounded-xl bg-white py-1 shadow-card ring-1 ring-slate-900/5"
                    >
                        @if (auth()->user()->isAdmin())
                            <a href="/admin" class="block px-4 py-2 text-sm hover:bg-slate-50">{{ __('messages.nav.admin') }}</a>
                        @endif
                        <a href="{{ route('my-courses') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">{{ __('messages.nav.my_courses') }}</a>
                        <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">{{ __('messages.nav.profile') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm text-rose-600 hover:bg-slate-50">
                                {{ __('messages.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-ghost hidden md:inline-flex">{{ __('messages.nav.login') }}</a>
                <a href="{{ route('register') }}" class="btn-primary hidden md:inline-flex">{{ __('messages.nav.register') }}</a>
            @endauth

            {{-- Mobile toggle --}}
            <button @click="open = !open" class="btn-ghost md:hidden" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </nav>

    {{-- Mobile menu --}}
    <div x-show="open" x-cloak class="border-t border-slate-200 bg-white md:hidden">
        <div class="container-app flex flex-col py-2">
            <a href="{{ route('home') }}" class="py-2 text-sm font-semibold">{{ __('messages.nav.home') }}</a>
            <a href="{{ route('courses.index') }}" class="py-2 text-sm font-semibold">{{ __('messages.nav.courses') }}</a>
            @auth
                <a href="{{ route('my-courses') }}" class="py-2 text-sm font-semibold">{{ __('messages.nav.my_courses') }}</a>
                @if (auth()->user()->isAdmin())
                    <a href="/admin" class="py-2 text-sm font-semibold">{{ __('messages.nav.admin') }}</a>
                @endif
                <a href="{{ route('profile') }}" class="py-2 text-sm font-semibold">{{ __('messages.nav.profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="py-2 text-start text-sm font-semibold text-rose-600">{{ __('messages.nav.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="py-2 text-sm font-semibold">{{ __('messages.nav.login') }}</a>
                <a href="{{ route('register') }}" class="py-2 text-sm font-semibold text-brand-700">{{ __('messages.nav.register') }}</a>
            @endauth
        </div>
    </div>
</header>
