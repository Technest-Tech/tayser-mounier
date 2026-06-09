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
                                    <li class="flex items-center gap-3 px-5 py-3.5">
                                        <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500">
                                            @if ($lesson->is_preview || $this->isEnrolled)
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                            @endif
                                        </span>
                                        <span class="flex-1 text-slate-700">{{ $lesson->title }}</span>
                                        @if ($lesson->is_preview)
                                            <span class="badge bg-emerald-50 text-emerald-700">{{ __('courses.detail.preview_available') }}</span>
                                        @endif
                                        @if ($lesson->duration)
                                            <span class="text-xs text-slate-400">{{ floor($lesson->duration / 60) }} {{ __('messages.common.minutes') }}</span>
                                        @endif
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
                <div class="aspect-video w-full bg-gradient-to-br from-brand-500 to-brand-800"></div>
                <div class="space-y-4 p-6">
                    @if (! $course->is_free)
                        <div class="text-3xl font-extrabold text-slate-900">
                            {{ number_format($course->price, 0) }}
                            <span class="text-base font-bold text-slate-400">{{ __('messages.common.currency') }}</span>
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
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
