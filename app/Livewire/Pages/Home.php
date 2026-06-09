<?php

namespace App\Livewire\Pages;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class Home extends Component
{
    #[Title('Tayser Mounier')]
    public function render(): View
    {
        $featured = Course::query()
            ->published()
            ->with('category')
            ->withCount('lessons')
            ->latest()
            ->take(6)
            ->get();

        return view('livewire.pages.home', [
            'featured' => $featured,
        ]);
    }
}
