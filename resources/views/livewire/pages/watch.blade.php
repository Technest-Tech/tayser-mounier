<div class="bg-slate-900">
    <div class="container-app py-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            {{-- Player + content --}}
            <div>
                <div class="overflow-hidden rounded-2xl bg-black shadow-card-hover">
                    <div class="aspect-video w-full">
                        <iframe
                            src="{{ $this->playerUrl }}"
                            class="h-full w-full"
                            loading="lazy"
                            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen"
                            allowfullscreen
                            wire:key="player-{{ $lesson->id }}"
                        ></iframe>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        @if ($lesson->is_preview)
                            <span class="badge bg-emerald-500/20 text-emerald-300">{{ __('courses.watch.preview_lesson') }}</span>
                        @endif
                        <h1 class="mt-2 text-2xl font-extrabold text-white">{{ $lesson->title }}</h1>
                        <a href="{{ route('courses.show', $course) }}" class="mt-1 inline-block text-sm text-slate-400 hover:text-white">
                            {{ $course->title }}
                        </a>
                    </div>

                    @if ($enrolled)
                        <button
                            wire:click="markComplete"
                            @class([
                                'btn',
                                'bg-emerald-500 text-white' => in_array($lesson->id, $completedIds),
                                'bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20' => ! in_array($lesson->id, $completedIds),
                            ])
                        >
                            @if (in_array($lesson->id, $completedIds))
                                ✓ {{ __('courses.watch.completed') }}
                            @else
                                {{ __('courses.watch.mark_complete') }}
                            @endif
                        </button>
                    @endif
                </div>

                {{-- Prev / next --}}
                <div class="mt-6 flex items-center justify-between gap-3">
                    @if ($previous && ($previous->is_preview || $enrolled))
                        <button wire:click="selectLesson({{ $previous->id }})" class="btn bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20">
                            ‹ {{ __('courses.watch.previous') }}
                        </button>
                    @else
                        <span></span>
                    @endif

                    @if ($next && ($next->is_preview || $enrolled))
                        <button wire:click="selectLesson({{ $next->id }})" class="btn bg-brand-600 text-white hover:bg-brand-700">
                            {{ __('courses.watch.next') }} ›
                        </button>
                    @endif
                </div>
            </div>

            {{-- Lesson list --}}
            <aside class="rounded-2xl bg-slate-800/60 p-4">
                <h2 class="px-2 pb-3 font-extrabold text-white">{{ __('courses.watch.lessons') }}</h2>
                <ul class="space-y-1">
                    @foreach ($lessons as $i => $l)
                        @php $locked = ! $l->is_preview && ! $enrolled; @endphp
                        <li>
                            <button
                                @if (! $locked) wire:click="selectLesson({{ $l->id }})" @endif
                                @disabled($locked)
                                @class([
                                    'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start transition',
                                    'bg-brand-600 text-white' => $l->id === $lesson->id,
                                    'text-slate-300 hover:bg-white/5' => $l->id !== $lesson->id && ! $locked,
                                    'cursor-not-allowed text-slate-500' => $locked,
                                ])
                            >
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-black/20 text-xs">
                                    @if (in_array($l->id, $completedIds))
                                        ✓
                                    @elseif ($locked)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 00-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span class="flex-1 truncate text-sm font-semibold">{{ $l->title }}</span>
                                @if ($l->is_preview)
                                    <span class="text-[10px] font-bold uppercase text-emerald-400">{{ __('courses.detail.preview') }}</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </div>
</div>
