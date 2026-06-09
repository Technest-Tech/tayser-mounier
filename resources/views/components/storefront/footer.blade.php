<footer class="mt-16 border-t border-slate-200 bg-white">
    <div class="container-app flex flex-col items-center justify-between gap-4 py-8 sm:flex-row">
        <div class="flex items-center gap-2">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 text-white font-extrabold">T</span>
            <span class="font-extrabold text-slate-900">{{ __('messages.app_name') }}</span>
        </div>
        <p class="text-sm text-slate-500">
            &copy; {{ date('Y') }} {{ __('messages.app_name') }}. {{ __('messages.tagline') }}.
        </p>
        <div class="flex items-center gap-4 text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-brand-700">{{ __('messages.nav.home') }}</a>
            <a href="{{ route('courses.index') }}" class="hover:text-brand-700">{{ __('messages.nav.courses') }}</a>
        </div>
    </div>
</footer>
