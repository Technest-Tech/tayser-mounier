<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.storefront')]
class CourseCatalog extends Component
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
        $courses = Course::query()
            ->published()
            ->with('category')
            ->withCount('lessons')
            ->search($this->search)
            ->when($this->category, fn ($q) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $this->category)
            ))
            ->when($this->access === 'free', fn ($q) => $q->free())
            ->when($this->access === 'paid', fn ($q) => $q->paid())
            ->latest()
            ->paginate(9);

        return view('livewire.pages.course-catalog', [
            'courses' => $courses,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
