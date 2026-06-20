<?php

namespace App\Livewire\Pages;

use App\Models\Book;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class Home extends Component
{
    public function render(): View
    {
        $featured = Course::query()
            ->published()
            ->with('category')
            ->withCount('lessons')
            ->latest()
            ->take(6)
            ->get();

        $featuredBooks = Book::query()
            ->published()
            ->with('category')
            ->latest()
            ->take(4)
            ->get();

        // Stat numbers fall back to live DB counts when no manual override is set.
        $courses  = Setting::get('stat_courses');
        $lessons  = Setting::get('stat_lessons');
        $students = Setting::get('stat_students');

        return view('livewire.pages.home', [
            'featured' => $featured,
            'featuredBooks' => $featuredBooks,
            'coursesCount' => $courses !== null && $courses !== ''
                ? (int) $courses
                : Course::published()->count(),
            'lessonsCount' => $lessons !== null && $lessons !== ''
                ? (int) $lessons
                : Lesson::whereHas('course', fn ($q) => $q->published())->count(),
            'studentsCount' => $students !== null && $students !== ''
                ? (int) $students
                : Enrollment::distinct('user_id')->count('user_id'),
        ])->title(Setting::get('site_title', __('messages.app_name')));
    }
}
