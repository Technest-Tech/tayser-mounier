<?php

namespace App\Livewire\Pages;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class BookShow extends Component
{
    public Book $book;

    public function mount(Book $book): void
    {
        abort_unless($book->isPublished(), 404);

        $this->book = $book->load('category');
    }

    public function render(): View
    {
        return view('livewire.pages.book-show');
    }
}
