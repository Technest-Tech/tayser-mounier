<?php

namespace App\Livewire\Pages;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.storefront')]
class BookCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?string $category = null;

    #[Url]
    public string $access = 'all'; // all | free | paid

    public function updating($name): void
    {
        // Reset pagination whenever a filter changes.
        if (in_array($name, ['search', 'category', 'access'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'access');
        $this->resetPage();
    }

    public function render(): View
    {
        $books = Book::query()
            ->published()
            ->with('category')
            ->search($this->search)
            ->when($this->category, fn ($q) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $this->category)
            ))
            ->when($this->access === 'free', fn ($q) => $q->free())
            ->when($this->access === 'paid', fn ($q) => $q->paid())
            ->latest()
            ->paginate(9);

        return view('livewire.pages.book-catalog', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
