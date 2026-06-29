@props(['course'])

<a
    href="{{ route('courses.show', $course) }}"
    class="card group flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-card-hover"
>
    {{-- Thumbnail --}}
    <div class="relative aspect-video w-full overflow-hidden bg-gradient-to-br from-brand-500 to-brand-800">
        @if ($course->thumbnail)
            <img
                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->thumbnail) }}"
                alt="{{ $course->title }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="grid h-full place-items-center text-white/90">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        @endif

        <div class="absolute top-3 {{ app()->getLocale() === 'ar' ? 'right-3' : 'left-3' }}">
            @if ($course->is_free)
                <span class="badge-free">{{ __('messages.common.free') }}</span>
            @else
                <span class="badge-paid">{{ __('messages.common.paid') }}</span>
            @endif
        </div>
    </div>

    {{-- Body --}}
    <div class="flex flex-1 flex-col p-4">
        @if ($course->category)
            <span class="text-xs font-bold uppercase tracking-wide text-brand-600">
                {{ $course->category->name }}
            </span>
        @endif

        <h3 class="mt-1 line-clamp-2 font-extrabold text-slate-900 group-hover:text-brand-700">
            {{ $course->title }}
        </h3>

        <div class="mt-auto flex items-center justify-between pt-4">
            <span class="text-sm text-slate-500">
                {{ __('courses.detail.total_lessons', ['count' => $course->lessons_count ?? $course->lessons()->count()]) }}
            </span>
            @unless ($course->is_free)
                <span class="text-end font-extrabold text-slate-900">
                    {{ number_format($course->price, 0) }} {{ __('messages.common.currency') }}
                    <span class="block text-xs font-bold text-slate-400">
                        {{ __('messages.common.currency_usd') }} {{ number_format($course->price_usd, 0) }}
                    </span>
                </span>
            @endunless
        </div>
    </div>
</a>
