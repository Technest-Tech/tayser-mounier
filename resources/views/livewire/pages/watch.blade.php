<div class="bg-slate-900">
    <div class="container-app py-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            {{-- Player + content --}}
            <div>
                @if ($lesson->hasVideo())
                <div class="overflow-hidden rounded-2xl bg-black shadow-card-hover">
                    <div class="aspect-video w-full">
                        @if($lesson->source->value === 'youtube')
                            {{-- Guarded YouTube player.
                                 The video plays through the YouTube IFrame API with native controls
                                 disabled (controls=0). A full-size transparent "shield" sits on top of
                                 the iframe so the student's mouse never reaches YouTube's own links —
                                 the title, the "Watch on YouTube" button shown on pause, the YouTube
                                 logo, and end-screen related-video thumbnails. That is what previously
                                 let students drag the video URL out into a new tab. Playback is driven
                                 entirely through our own controls below. --}}
                            <div
                                x-data="ytGuardedPlayer('{{ $lesson->video_id }}', 'yt-{{ $lesson->id }}')"
                                wire:key="yt-player-{{ $lesson->id }}"
                                x-ref="container"
                                class="group relative h-full w-full select-none bg-black"
                                @contextmenu.prevent
                            >
                                {{-- Poster: shown until the student clicks play --}}
                                <button
                                    type="button"
                                    x-show="!started"
                                    @click="start()"
                                    class="absolute inset-0 h-full w-full cursor-pointer"
                                >
                                    <img
                                        src="https://img.youtube.com/vi/{{ $lesson->video_id }}/hqdefault.jpg"
                                        class="h-full w-full object-cover"
                                        alt=""
                                        draggable="false"
                                    >
                                    <span class="absolute inset-0 flex items-center justify-center">
                                        <span class="flex h-16 w-16 items-center justify-center rounded-full bg-red-600 opacity-90 shadow-xl transition-opacity hover:opacity-100">
                                            <svg class="ml-1 h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </span>
                                    </span>
                                </button>

                                {{-- Player target: the YouTube IFrame API replaces this div with the iframe --}}
                                <div x-show="started" class="absolute inset-0 h-full w-full">
                                    <div id="yt-{{ $lesson->id }}" class="h-full w-full"></div>
                                </div>

                                {{-- Click-shield: covers the whole iframe so no YouTube link is ever
                                     reachable by the mouse. Click toggles play/pause, double-click
                                     toggles fullscreen. --}}
                                <div
                                    x-show="started"
                                    class="absolute inset-0 z-10"
                                    @click="toggle()"
                                    @dblclick="toggleFullscreen()"
                                    @dragstart.prevent
                                    @contextmenu.prevent
                                ></div>

                                {{-- Custom controls --}}
                                <div
                                    x-show="started"
                                    x-cloak
                                    dir="ltr"
                                    class="absolute inset-x-0 bottom-0 z-20 flex items-center gap-3 bg-gradient-to-t from-black/80 to-transparent px-4 pb-2 pt-8 text-white opacity-0 transition-opacity group-hover:opacity-100"
                                    :class="{ 'opacity-100': !playing }"
                                >
                                    <button type="button" @click="toggle()" class="shrink-0" aria-label="Play / pause">
                                        <svg x-show="!playing" class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <svg x-show="playing" x-cloak class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
                                    </button>

                                    <div
                                        class="relative h-1.5 flex-1 cursor-pointer rounded-full bg-white/30"
                                        @click="seek($event)"
                                    >
                                        <div class="absolute inset-y-0 left-0 rounded-full bg-red-600" :style="`width: ${progress}%`"></div>
                                    </div>

                                    <span class="shrink-0 font-mono text-xs tabular-nums" x-text="`${fmt(current)} / ${fmt(duration)}`"></span>

                                    {{-- Playback speed --}}
                                    <div class="relative shrink-0" @click.outside="showRates = false">
                                        <button
                                            type="button"
                                            @click="showRates = !showRates"
                                            class="rounded px-2 py-0.5 text-xs font-bold tabular-nums hover:bg-white/20"
                                            aria-label="Playback speed"
                                            x-text="rate + '×'"
                                        ></button>
                                        <div
                                            x-show="showRates"
                                            x-cloak
                                            class="absolute bottom-full right-0 mb-2 w-20 overflow-hidden rounded-lg bg-slate-900/95 py-1 text-xs shadow-xl ring-1 ring-white/10"
                                        >
                                            <template x-for="r in rates" :key="r">
                                                <button
                                                    type="button"
                                                    @click="setRate(r)"
                                                    class="block w-full px-3 py-1.5 text-right font-semibold tabular-nums hover:bg-white/10"
                                                    :class="r === rate ? 'text-red-500' : 'text-white'"
                                                    x-text="r + '×'"
                                                ></button>
                                            </template>
                                        </div>
                                    </div>

                                    <button type="button" @click="toggleFullscreen()" class="shrink-0" aria-label="Fullscreen">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9M3.75 20.25h4.5m-4.5 0v-4.5m0 4.5L9 15m11.25 5.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <iframe
                                src="{{ $this->playerUrl }}"
                                class="h-full w-full"
                                loading="lazy"
                                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture; fullscreen"
                                allowfullscreen
                                wire:key="player-{{ $lesson->id }}"
                            ></iframe>
                        @endif
                    </div>
                </div>
                @endif

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

                {{-- Voice file --}}
                @if ($lesson->hasAudio())
                    <div class="mt-6 rounded-2xl bg-slate-800/60 p-5" wire:key="audio-{{ $lesson->id }}">
                        <div class="mb-3 flex items-center gap-2 text-white">
                            <svg class="h-5 w-5 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z"/></svg>
                            <h2 class="font-bold">{{ __('courses.watch.audio') }}</h2>
                        </div>
                        <audio
                            controls
                            controlslist="nodownload"
                            class="w-full"
                            src="{{ route('lessons.audio', [$course, $lesson]) }}"
                        >
                            {{ __('courses.watch.audio_unsupported') }}
                        </audio>
                        <a
                            href="{{ route('lessons.audio.download', [$course, $lesson]) }}"
                            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            {{ __('courses.watch.audio_download') }}
                        </a>
                    </div>
                @endif

                {{-- PDF document --}}
                @if ($lesson->hasPdf())
                    <div class="mt-6 rounded-2xl bg-slate-800/60 p-5" wire:key="pdf-{{ $lesson->id }}">
                        <div class="mb-3 flex items-center justify-between gap-2 text-white">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                <h2 class="font-bold">{{ __('courses.watch.pdf') }}</h2>
                            </div>
                            <a
                                href="{{ route('lessons.pdf', [$course, $lesson]) }}"
                                target="_blank"
                                rel="noopener"
                                class="text-sm font-semibold text-brand-400 hover:text-brand-300"
                            >
                                {{ __('courses.watch.open_pdf') }} ↗
                            </a>
                        </div>
                        <div class="aspect-[3/4] w-full overflow-hidden rounded-xl bg-white sm:aspect-[4/3]">
                            <iframe
                                src="{{ route('lessons.pdf', [$course, $lesson]) }}#toolbar=0"
                                class="h-full w-full"
                                loading="lazy"
                                title="{{ $lesson->title }}"
                            ></iframe>
                        </div>
                    </div>
                @endif

                {{-- No content yet --}}
                @unless ($lesson->hasContent())
                    <div class="mt-6 rounded-2xl border border-dashed border-white/15 bg-slate-800/40 p-8 text-center text-slate-400">
                        {{ __('courses.watch.no_content') }}
                    </div>
                @endunless

                {{-- Lesson quiz / exam --}}
                @if ($questions->isNotEmpty())
                    <div class="mt-8" wire:key="quiz-{{ $lesson->id }}">
                        <div class="rounded-2xl bg-slate-800/60 p-6 ring-1 ring-white/10">
                            {{-- Header --}}
                            <div class="flex items-start gap-3">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-500/20 text-brand-300">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-xl font-extrabold text-white">{{ __('quiz.title') }}</h2>
                                    <p class="mt-0.5 text-sm text-slate-400">
                                        @if ($quizSubmitted)
                                            {{ __('quiz.questions_count', ['count' => $questions->count()]) }}
                                        @else
                                            {{ __('quiz.subtitle') }}
                                        @endif
                                    </p>
                                </div>

                                {{-- Certificate: shown once the quiz is finished, for signed-in students. --}}
                                @auth
                                    @if ($quizSubmitted)
                                        <a
                                            href="{{ route('lessons.certificate', [$course, $lesson]) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="btn ms-auto self-center inline-flex items-center gap-2 bg-accent-500 text-white shadow-lg shadow-accent-600/30 hover:bg-accent-600"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9a9.06 9.06 0 0 0-1.5.124m12-.124a9.06 9.06 0 0 1 1.5.124M9 16.5h6m-9 1.875a3.375 3.375 0 0 0 6.75 0V9.75a3.375 3.375 0 0 0-6.75 0v8.625Z"/><circle cx="12" cy="8.25" r="3.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            {{ __('certificate.download') }}
                                        </a>
                                    @endif
                                @endauth
                            </div>

                            @if (! $quizSubmitted)
                                {{-- Take the quiz --}}
                                <div class="mt-6 space-y-5">
                                    @foreach ($questions as $qi => $q)
                                        <div class="rounded-xl bg-slate-900/50 p-5">
                                            <span class="text-xs font-bold text-brand-300">
                                                {{ __('quiz.question_num', ['num' => $qi + 1, 'total' => $questions->count()]) }}
                                            </span>
                                            <p class="mt-1 text-base font-bold text-white">{{ $q->question }}</p>

                                            <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                                                @foreach ($q->options as $opt)
                                                    @php $selected = ($answers[$q->id] ?? null) == $opt->id; @endphp
                                                    <button
                                                        type="button"
                                                        wire:click="chooseOption({{ $q->id }}, {{ $opt->id }})"
                                                        @class([
                                                            'flex items-center gap-3 rounded-xl border px-4 py-3 text-start text-sm font-semibold transition',
                                                            'border-brand-400 bg-brand-500/20 text-white' => $selected,
                                                            'border-white/10 bg-white/5 text-slate-200 hover:border-white/25 hover:bg-white/10' => ! $selected,
                                                        ])
                                                    >
                                                        <span @class([
                                                            'grid h-5 w-5 shrink-0 place-items-center rounded-full border-2 transition',
                                                            'border-brand-300 bg-brand-400' => $selected,
                                                            'border-slate-500' => ! $selected,
                                                        ])>
                                                            @if ($selected)
                                                                <span class="h-2 w-2 rounded-full bg-white"></span>
                                                            @endif
                                                        </span>
                                                        <span class="flex-1">{{ $opt->text }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @php $allAnswered = collect($questions)->every(fn ($q) => isset($answers[$q->id])); @endphp
                                <div class="mt-6 flex flex-wrap items-center gap-3">
                                    <button
                                        wire:click="submitQuiz"
                                        @disabled(! $allAnswered)
                                        @class([
                                            'btn',
                                            'bg-brand-600 text-white hover:bg-brand-700' => $allAnswered,
                                            'cursor-not-allowed bg-white/10 text-slate-400' => ! $allAnswered,
                                        ])
                                    >
                                        {{ __('quiz.submit') }}
                                    </button>
                                    @unless ($allAnswered)
                                        <span class="text-sm text-slate-400">{{ __('quiz.answer_all_first') }}</span>
                                    @endunless
                                </div>
                            @else
                                {{-- Results --}}
                                @php
                                    $total = $questions->count();
                                    $pct = $total ? (int) round($quizScore / $total * 100) : 0;
                                    $ringColor = $pct === 100 ? '#10b981' : ($pct >= 50 ? '#6366f1' : '#f43f5e');
                                    $msg = $pct === 100 ? __('quiz.result_excellent') : ($pct >= 50 ? __('quiz.result_good') : __('quiz.result_try_again'));
                                @endphp

                                <div class="mt-6 flex flex-col items-center gap-5 rounded-2xl bg-slate-900/50 p-6 text-center sm:flex-row sm:text-start">
                                    <div
                                        class="relative grid h-28 w-28 shrink-0 place-items-center rounded-full"
                                        style="background: conic-gradient({{ $ringColor }} {{ $pct }}%, rgba(255,255,255,.10) 0);"
                                    >
                                        <div class="grid h-[5.5rem] w-[5.5rem] place-items-center rounded-full bg-slate-900">
                                            <span class="text-2xl font-extrabold text-white">{{ $pct }}%</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-400">{{ __('quiz.your_result') }}</h3>
                                        <p class="mt-1 text-2xl font-extrabold" style="color: {{ $ringColor }};">{{ $msg }}</p>
                                        <p class="mt-1 text-sm text-slate-300">{{ __('quiz.score_line', ['score' => $quizScore, 'total' => $total]) }}</p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-4">
                                    @foreach ($questions as $qi => $q)
                                        @php
                                            $correct = $q->options->firstWhere('is_correct', true);
                                            $chosenId = $answers[$q->id] ?? null;
                                            $isRight = $correct && (int) $chosenId === $correct->id;
                                        @endphp
                                        <div class="rounded-xl bg-slate-900/50 p-5">
                                            <div class="flex items-start justify-between gap-3">
                                                <p class="text-base font-bold text-white">
                                                    <span class="text-slate-400">{{ $qi + 1 }}.</span> {{ $q->question }}
                                                </p>
                                                <span @class([
                                                    'badge shrink-0',
                                                    'bg-emerald-500/20 text-emerald-300' => $isRight,
                                                    'bg-rose-500/20 text-rose-300' => ! $isRight,
                                                ])>
                                                    @if ($isRight) ✓ {{ __('quiz.correct') }} @else ✕ {{ __('quiz.incorrect') }} @endif
                                                </span>
                                            </div>

                                            <div class="mt-3 space-y-2">
                                                @foreach ($q->options as $opt)
                                                    @php
                                                        $optCorrect = $opt->is_correct;
                                                        $optChosen = (int) $chosenId === $opt->id;
                                                    @endphp
                                                    <div @class([
                                                        'flex items-center gap-3 rounded-lg border px-4 py-2.5 text-sm',
                                                        'border-emerald-400/40 bg-emerald-500/10 text-emerald-200' => $optCorrect,
                                                        'border-rose-400/40 bg-rose-500/10 text-rose-200' => $optChosen && ! $optCorrect,
                                                        'border-white/10 bg-white/5 text-slate-400' => ! $optCorrect && ! $optChosen,
                                                    ])>
                                                        <span class="shrink-0 font-bold">
                                                            @if ($optCorrect) ✓ @elseif ($optChosen) ✕ @else • @endif
                                                        </span>
                                                        <span class="flex-1 font-semibold">{{ $opt->text }}</span>
                                                        @if ($optChosen)
                                                            <span class="shrink-0 text-xs font-bold opacity-80">{{ __('quiz.your_answer') }}</span>
                                                        @elseif ($optCorrect)
                                                            <span class="shrink-0 text-xs font-bold opacity-80">{{ __('quiz.correct_answer') }}</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6">
                                    <button wire:click="retakeQuiz" class="btn bg-white/10 text-white ring-1 ring-white/20 hover:bg-white/20">
                                        ↺ {{ __('quiz.retake') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

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
                        @php $locked = ! $course->is_free && ! $l->is_preview && ! $enrolled; @endphp
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
                                @if (! $course->is_free && $l->is_preview)
                                    <span class="text-[10px] font-bold uppercase text-emerald-400">{{ __('courses.detail.preview') }}</span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </div>

    {{-- Guarded YouTube player logic (see the player markup above). Defined once. --}}
    <script>
        window.loadYouTubeApi = window.loadYouTubeApi || function () {
            if (window.__ytApiPromise) return window.__ytApiPromise;
            window.__ytApiPromise = new Promise(function (resolve) {
                if (window.YT && window.YT.Player) return resolve(window.YT);
                var prev = window.onYouTubeIframeAPIReady;
                window.onYouTubeIframeAPIReady = function () {
                    if (typeof prev === 'function') prev();
                    resolve(window.YT);
                };
                var tag = document.createElement('script');
                tag.src = 'https://www.youtube.com/iframe_api';
                document.head.appendChild(tag);
            });
            return window.__ytApiPromise;
        };

        window.ytGuardedPlayer = window.ytGuardedPlayer || function (videoId, elId) {
            return {
                started: false,
                playing: false,
                current: 0,
                duration: 0,
                progress: 0,
                player: null,
                timer: null,
                rate: 1,
                showRates: false,
                rates: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2],

                start() {
                    if (this.started) return;
                    this.started = true;
                    window.loadYouTubeApi().then((YT) => {
                        this.player = new YT.Player(elId, {
                            videoId: videoId,
                            host: 'https://www.youtube-nocookie.com',
                            playerVars: {
                                autoplay: 1,
                                controls: 0,
                                modestbranding: 1,
                                rel: 0,
                                disablekb: 1,
                                fs: 0,
                                playsinline: 1,
                                iv_load_policy: 3,
                            },
                            events: {
                                onReady: (e) => {
                                    // Belt-and-suspenders: the iframe itself ignores the mouse, so even
                                    // if the shield is removed the YouTube links can't be dragged.
                                    e.target.getIframe().style.pointerEvents = 'none';
                                    this.duration = e.target.getDuration();
                                    if (this.rate !== 1 && e.target.setPlaybackRate) {
                                        e.target.setPlaybackRate(this.rate);
                                    }
                                    e.target.playVideo();
                                    this.startTracking();
                                },
                                onStateChange: (e) => {
                                    this.playing = e.data === YT.PlayerState.PLAYING;
                                },
                            },
                        });
                    });
                },

                startTracking() {
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if (!this.player || !this.player.getCurrentTime) return;
                        this.current = this.player.getCurrentTime();
                        this.duration = this.player.getDuration() || this.duration;
                        this.progress = this.duration ? (this.current / this.duration) * 100 : 0;
                    }, 250);
                },

                toggle() {
                    if (!this.player) return;
                    this.playing ? this.player.pauseVideo() : this.player.playVideo();
                },

                setRate(r) {
                    this.rate = r;
                    this.showRates = false;
                    if (this.player && this.player.setPlaybackRate) {
                        this.player.setPlaybackRate(r);
                    }
                },

                seek(event) {
                    if (!this.player || !this.duration) return;
                    const rect = event.currentTarget.getBoundingClientRect();
                    const ratio = Math.min(Math.max((event.clientX - rect.left) / rect.width, 0), 1);
                    const time = ratio * this.duration;
                    this.player.seekTo(time, true);
                    this.current = time;
                    this.progress = ratio * 100;
                },

                toggleFullscreen() {
                    const el = this.$refs.container;
                    if (document.fullscreenElement) {
                        document.exitFullscreen();
                    } else if (el && el.requestFullscreen) {
                        el.requestFullscreen();
                    }
                },

                fmt(seconds) {
                    seconds = Math.floor(seconds || 0);
                    const m = Math.floor(seconds / 60);
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return m + ':' + s;
                },
            };
        };
    </script>
</div>
