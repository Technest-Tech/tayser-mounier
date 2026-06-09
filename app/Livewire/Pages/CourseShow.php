<?php

namespace App\Livewire\Pages;

use App\Actions\EnrollInFreeCourseAction;
use App\Actions\RedeemAccessCodeAction;
use App\Exceptions\AccessCodeException;
use App\Models\Course;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class CourseShow extends Component
{
    public Course $course;

    public string $code = '';

    public bool $showCodeForm = false;

    public function mount(Course $course): void
    {
        abort_unless($course->isPublished(), 404);

        $this->course = $course->load(['category', 'lessons']);
    }

    public function getIsEnrolledProperty(): bool
    {
        return auth()->check() && auth()->user()->isEnrolledIn($this->course);
    }

    public function enrollFree(EnrollInFreeCourseAction $action): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        abort_unless($this->course->is_free, 403);

        $action->execute(auth()->user(), $this->course);

        $this->redirectToFirstLesson();
    }

    public function redeem(RedeemAccessCodeAction $action): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $this->validate(['code' => 'required|string|min:4']);

        try {
            $action->execute(auth()->user(), $this->code, $this->course);
        } catch (AccessCodeException $e) {
            throw ValidationException::withMessages(['code' => $e->getMessage()]);
        }

        session()->flash('status', __('codes.success'));

        $this->redirectToFirstLesson();
    }

    protected function redirectToFirstLesson(): void
    {
        $first = $this->course->lessons()->orderBy('order')->first();

        $this->redirectRoute('courses.watch', [
            'course' => $this->course->slug,
            'lesson' => $first?->id,
        ], navigate: true);
    }

    public function render(): View
    {
        $lessons = $this->course->lessons()->orderBy('order')->get();

        return view('livewire.pages.course-show', [
            'lessons' => $lessons,
            'sections' => $lessons->groupBy('section'),
        ]);
    }
}
