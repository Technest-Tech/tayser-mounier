<?php

namespace App\Livewire\Pages;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class MyCourses extends Component
{
    public function render(): View
    {
        $courses = Course::query()
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', auth()->id()))
            ->with('category')
            ->withCount('lessons')
            ->latest()
            ->get();

        return view('livewire.pages.my-courses', [
            'courses' => $courses,
        ]);
    }
}
