<div class="container-app py-10">
    <div class="grid gap-10 lg:grid-cols-[1fr_360px]">
        {{-- Main --}}
        <div>
            <nav class="mb-4 text-sm text-slate-500">
                <a href="{{ route('courses.index') }}" class="hover:text-brand-700">{{ __('courses.title') }}</a>
                @if ($course->category)
                    <span class="px-1">/</span>
                    <span>{{ $course->category->name }}</span>
                @endif
            </nav>

            <div class="flex flex-wrap items-center gap-3">
                @if ($course->is_free)
                    <span class="badge-free">{{ __('messages.common.free') }}</span>
                @else
                    <span class="badge-paid">{{ __('messages.common.paid') }}</span>
                @endif
            </div>

            <h1 class="mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl">{{ $course->title }}</h1>

            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                <span>{{ __('courses.detail.total_lessons', ['count' => $lessons->count()]) }}</span>
            </div>

            {{-- About --}}
            <section class="mt-8">
                <h2 class="text-xl font-extrabold text-slate-900">{{ __('courses.detail.about') }}</h2>
                <div class="prose prose-slate mt-3 max-w-none whitespace-pre-line text-slate-600">
                    {{ $course->description }}
                </div>
            </section>

            {{-- Curriculum --}}
            <section class="mt-10">
                <h2 class="text-xl font-extrabold text-slate-900">{{ __('courses.detail.curriculum') }}</h2>
                <div class="mt-4 space-y-6">
                    @foreach ($sections as $sectionName => $sectionLessons)
                        <div class="card overflow-hidden">
                            @if ($sectionName)
                                <div class="border-b border-slate-100 bg-slate-50 px-5 py-3 font-bold text-slate-800">
                                    {{ $sectionName }}
                                </div>
                            @endif
                            <ul class="divide-y divide-slate-100">
                                @foreach ($sectionLessons as $lesson)
                                    @php $accessible = $course->is_free || $lesson->is_preview || $this->isEnrolled; @endphp
                                    <li>
                                        {{-- Accessible lessons (free course, free preview, or enrolled) link
                                             straight to the player; locked lessons stay non-clickable. --}}
                                        <{{ $accessible ? 'a' : 'div' }}
                                            @if ($accessible) href="{{ route('courses.watch', ['course' => $course->slug, 'lesson' => $lesson->id]) }}" wire:navigate @endif
                                            @class([
                                                'flex items-center gap-3 px-5 py-3.5',
                                                'transition hover:bg-slate-50' => $accessible,
                                            ])
                                        >
                                            <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500">
                                                @if ($accessible)
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                                @endif
                                            </span>
                                            <span class="flex-1 text-slate-700">{{ $lesson->title }}</span>
                                            @if (! $course->is_free && $lesson->is_preview)
                                                <span class="badge bg-emerald-50 text-emerald-700">{{ __('courses.detail.preview_available') }}</span>
                                            @endif
                                            @if ($lesson->duration)
                                                <span class="text-xs text-slate-400">{{ floor($lesson->duration / 60) }} {{ __('messages.common.minutes') }}</span>
                                            @endif
                                        </{{ $accessible ? 'a' : 'div' }}>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Sidebar / purchase card --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden">
                <div class="aspect-video w-full overflow-hidden bg-gradient-to-br from-brand-500 to-brand-800">
                    @if ($course->thumbnail)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->thumbnail) }}"
                            alt="{{ $course->title }}"
                            class="h-full w-full object-cover"
                        >
                    @endif
                </div>
                <div class="space-y-4 p-6">
                    @if (! $course->is_free)
                        <div>
                            <div class="text-3xl font-extrabold text-slate-900">
                                {{ number_format($course->price, 0) }}
                                <span class="text-base font-bold text-slate-400">{{ __('messages.common.currency') }}</span>
                            </div>
                            <div class="mt-1 text-lg font-bold text-slate-500">
                                {{ __('messages.common.currency_usd') }} {{ number_format($course->price_usd, 0) }}
                                <span class="text-sm font-semibold text-slate-400">{{ __('courses.detail.price_outside') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($this->isEnrolled)
                        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm font-bold text-emerald-700">
                            ✓ {{ __('courses.detail.enrolled') }}
                        </div>
                        <a href="{{ route('courses.watch', ['course' => $course->slug, 'lesson' => $lessons->first()?->id]) }}" class="btn-primary w-full">
                            {{ __('courses.detail.continue_learning') }}
                        </a>
                    @elseif ($course->is_free)
                        @auth
                            <button wire:click="enrollFree" class="btn-primary w-full">
                                {{ __('courses.detail.enroll_free') }}
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary w-full">{{ __('courses.detail.login_to_enroll') }}</a>
                        @endauth
                    @else
                        {{-- Paid: redeem a code --}}
                        @auth
                            <div x-data="{ open: @js($errors->has('code')) }">
                                <button x-show="!open" @click="open = true" class="btn-primary w-full">
                                    {{ __('courses.detail.unlock') }}
                                </button>
                                <div x-show="open" x-cloak class="space-y-3">
                                    <p class="text-sm text-slate-500">{{ __('codes.unlock_subtitle') }}</p>
                                    <input
                                        type="text"
                                        wire:model="code"
                                        wire:keydown.enter="redeem"
                                        placeholder="{{ __('codes.placeholder') }}"
                                        class="input text-center font-mono tracking-widest uppercase"
                                        autocomplete="off"
                                    >
                                    @error('code')
                                        <p class="text-sm font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                    <button wire:click="redeem" wire:loading.attr="disabled" class="btn-primary w-full">
                                        <span wire:loading.remove wire:target="redeem">{{ __('codes.submit') }}</span>
                                        <span wire:loading wire:target="redeem">…</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary w-full">{{ __('courses.detail.login_to_enroll') }}</a>
                        @endauth

                        @if(config('services.whatsapp.number'))
                            <a
                                href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('codes.whatsapp_subscribe_message', ['course' => $course->title])) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                style="background-color:#25D366"
                                class="flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white w-full transition"
                                onmouseover="this.style.backgroundColor='#1ebe5d'" onmouseout="this.style.backgroundColor='#25D366'"
                            >
                                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                {{ __('codes.whatsapp_subscribe') }}
                            </a>

                            <a
                                href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('codes.whatsapp_message', ['course' => $course->title])) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-center text-sm font-semibold text-emerald-700 hover:text-emerald-800 w-full"
                            >
                                {{ __('codes.whatsapp_contact') }}
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
