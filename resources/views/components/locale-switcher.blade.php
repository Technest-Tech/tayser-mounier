@php
    $current = app()->getLocale();
    $locales = config('localization.supported');
@endphp
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="btn-ghost px-2.5" aria-label="Language">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <span class="text-xs font-bold uppercase">{{ $current }}</span>
    </button>
    <div
        x-show="open" x-cloak @click.outside="open = false"
        class="absolute end-0 mt-2 w-36 overflow-hidden rounded-xl bg-white py-1 shadow-card ring-1 ring-slate-900/5"
    >
        @foreach ($locales as $code => $meta)
            <form method="POST" action="{{ route('locale.update', $code) }}">
                @csrf
                <button
                    type="submit"
                    class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50 {{ $code === $current ? 'font-extrabold text-brand-700' : 'text-slate-600' }}"
                >
                    <span>{{ $meta['name'] }}</span>
                    @if ($code === $current)
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
