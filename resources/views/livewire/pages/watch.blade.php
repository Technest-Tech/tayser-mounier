<div class="bg-slate-900">
    <div class="container-app py-6">
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            {{-- Player + content --}}
            <div>
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
