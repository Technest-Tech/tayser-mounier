<div class="container-app py-10">
    <div class="grid gap-10 lg:grid-cols-[1fr_360px]">
        {{-- Main --}}
        <div>
            <nav class="mb-4 text-sm text-slate-500">
                <a href="{{ route('books.index') }}" class="hover:text-brand-700">{{ __('books.title') }}</a>
                @if ($book->category)
                    <span class="px-1">/</span>
                    <span>{{ $book->category->name }}</span>
                @endif
            </nav>

            <div class="flex flex-wrap items-center gap-3">
                @if ($book->is_free)
                    <span class="badge-free">{{ __('messages.common.free') }}</span>
                @else
                    <span class="badge-paid">{{ __('messages.common.paid') }}</span>
                    @if ($book->sample)
                        <span class="badge bg-emerald-50 text-emerald-700">{{ __('books.detail.free_sample') }}</span>
                    @endif
                @endif
            </div>

            <h1 class="mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl">{{ $book->title }}</h1>

            @if ($book->author)
                <p class="mt-2 text-slate-500">{{ __('books.detail.by', ['author' => $book->author]) }}</p>
            @endif

            {{-- About --}}
            @if ($book->description)
                <section class="mt-8">
                    <h2 class="text-xl font-extrabold text-slate-900">{{ __('books.detail.about') }}</h2>
                    <div class="prose prose-slate mt-3 max-w-none whitespace-pre-line text-slate-600">
                        {{ $book->description }}
                    </div>
                </section>
            @endif

            {{-- In-site preview --}}
            @if ($book->hasAccessibleFile())
                <section class="mt-10">
                    <h2 class="text-xl font-extrabold text-slate-900">
                        {{ $book->is_free ? __('books.detail.read_online') : __('books.detail.read_sample') }}
                    </h2>
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                        <iframe
                            src="{{ route('books.preview', $book) }}#toolbar=0"
                            class="h-[70vh] w-full"
                            title="{{ $book->title }}"
                            loading="lazy"
                        ></iframe>
                    </div>
                </section>
            @endif
        </div>

        {{-- Sidebar / action card --}}
        <aside class="lg:sticky lg:top-24 lg:self-start">
            <div class="card overflow-hidden">
                {{-- Cover --}}
                <div class="aspect-[3/4] w-full bg-gradient-to-br from-brand-500 to-brand-800">
                    @if ($book->cover)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($book->cover) }}"
                            alt="{{ $book->title }}"
                            class="h-full w-full object-cover"
                        >
                    @endif
                </div>

                <div class="space-y-4 p-6">
                    @if (! $book->is_free)
                        <div class="text-3xl font-extrabold text-slate-900">
                            {{ number_format($book->price, 0) }}
                            <span class="text-base font-bold text-slate-400">{{ __('messages.common.currency') }}</span>
                        </div>
                    @endif

                    @if ($book->is_free)
                        {{-- Free book: read + download the full book --}}
                        @if ($book->hasAccessibleFile())
                            <a href="{{ route('books.download', $book) }}" class="btn-primary w-full">
                                {{ __('books.detail.download') }}
                            </a>
                            <a href="{{ route('books.preview', $book) }}" target="_blank" rel="noopener" class="btn-outline w-full">
                                {{ __('books.detail.read_online') }}
                            </a>
                        @else
                            <p class="text-sm text-slate-500">{{ __('books.detail.coming_soon') }}</p>
                        @endif
                    @else
                        {{-- Paid book: buy via WhatsApp, free sample to read/download --}}
                        @if (config('services.whatsapp.number'))
                            <a
                                href="https://wa.me/{{ config('services.whatsapp.number') }}?text={{ urlencode(__('books.detail.whatsapp_message', ['title' => $book->title])) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                style="background-color:#25D366"
                                class="flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white w-full transition"
                                onmouseover="this.style.backgroundColor='#1ebe5d'" onmouseout="this.style.backgroundColor='#25D366'"
                            >
                                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                {{ __('books.detail.buy_whatsapp') }}
                            </a>
                            <p class="text-center text-xs text-slate-400">{{ __('books.detail.buy_note') }}</p>
                        @endif

                        @if ($book->sample)
                            <div class="border-t border-slate-100 pt-4">
                                <p class="mb-2 text-sm font-bold text-slate-700">{{ __('books.detail.free_sample') }}</p>
                                <a href="{{ route('books.download', $book) }}" class="btn-outline w-full">
                                    {{ __('books.detail.download_sample') }}
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
