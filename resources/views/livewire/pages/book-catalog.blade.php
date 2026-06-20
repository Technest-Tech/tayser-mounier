<div class="container-app py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">{{ __('books.title') }}</h1>
        <p class="mt-2 text-slate-500">{{ __('books.subtitle') }}</p>
    </div>

    <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
        {{-- Filters --}}
        <aside class="space-y-6">
            {{-- Search --}}
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('messages.common.search') }}</label>
                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('books.search_placeholder') }}"
                    class="input"
                >
            </div>

            {{-- Access --}}
            <div>
                <span class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('messages.common.price') }}</span>
                <div class="flex flex-wrap gap-2">
                    @foreach (['all' => 'all', 'free' => 'free', 'paid' => 'paid'] as $value => $label)
                        <button
                            wire:click="$set('access', '{{ $value }}')"
                            class="badge px-3 py-1.5 {{ $access === $value ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200' }}"
                        >
                            {{ __('messages.common.' . $label) }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Categories --}}
            <div>
                <span class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('messages.common.category') }}</span>
                <div class="space-y-1">
                    <button
                        wire:click="$set('category', null)"
                        class="block w-full rounded-lg px-3 py-1.5 text-start text-sm {{ $category === null ? 'bg-brand-50 font-bold text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        {{ __('messages.common.all') }}
                    </button>
                    @foreach ($categories as $cat)
                        <button
                            wire:click="$set('category', '{{ $cat->slug }}')"
                            class="block w-full rounded-lg px-3 py-1.5 text-start text-sm {{ $category === $cat->slug ? 'bg-brand-50 font-bold text-brand-700' : 'text-slate-600 hover:bg-slate-100' }}"
                        >
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <button wire:click="clearFilters" class="btn-ghost text-sm">
                ✕ {{ __('messages.common.filter') }}: {{ __('messages.common.all') }}
            </button>
        </aside>

        {{-- Results --}}
        <div>
            <div wire:loading.delay class="mb-4 text-sm text-slate-400">…</div>

            @if ($books->isEmpty())
                <div class="card grid place-items-center p-16 text-center">
                    <p class="text-slate-500">{{ __('books.no_results') }}</p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($books as $book)
                        <x-book-card :book="$book" :wire:key="'book-'.$book->id" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $books->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
