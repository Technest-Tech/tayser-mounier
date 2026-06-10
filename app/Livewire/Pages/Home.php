<?php

namespace App\Livewire\Pages;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class Home extends Component
{
    #[Title('التيسير — أحمد منير')]
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
            'coursesCount' => Course::published()->count(),
            'lessonsCount' => Lesson::whereHas('course', fn ($q) => $q->published())->count(),
            'studentsCount' => Enrollment::distinct('user_id')->count('user_id'),
        ]);
    }
}
