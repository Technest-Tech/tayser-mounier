@props(['book'])

<a
    href="{{ route('books.show', $book) }}"
    class="card group flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-card-hover"
>
    {{-- Cover --}}
    <div class="relative aspect-[3/4] w-full overflow-hidden bg-gradient-to-br from-brand-500 to-brand-800">
        @if ($book->cover)
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($book->cover) }}"
                alt="{{ $book->title }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="grid h-full place-items-center text-white/90">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
        @endif

        <div class="absolute top-3 {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }}">
            @if ($book->is_free)
                <span class="badge-free">{{ __('messages.common.free') }}</span>
            @else
                <span class="badge-paid">{{ __('messages.common.paid') }}</span>
            @endif
        </div>
    </div>

    {{-- Body --}}
    <div class="flex flex-1 flex-col p-4">
        @if ($book->category)
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">
                {{ $book->category->name }}
            </span>
        @endif

        <h3 class="mt-1 line-clamp-2 font-extrabold text-slate-900 group-hover:text-brand-700">
            {{ $book->title }}
        </h3>

        @if ($book->author)
            <span class="mt-1 text-sm text-slate-500">{{ __('books.detail.by', ['author' => $book->author]) }}</span>
        @endif

        <div class="mt-auto flex items-center justify-between pt-4">
            @if (! $book->is_free && $book->sample)
                <span class="badge bg-emerald-50 text-emerald-700">{{ __('books.detail.free_sample') }}</span>
            @else
                <span></span>
            @endif
            @unless ($book->is_free)
                <span class="font-extrabold text-slate-900">
                    {{ number_format($book->price, 0) }} {{ __('messages.common.currency') }}
                </span>
            @endunless
        </div>
    </div>
</a>
