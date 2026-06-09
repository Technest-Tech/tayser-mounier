<?php

namespace App\Livewire\Pages;

use App\Enums\LessonSource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\Bunny\BunnySignedUrlService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class Watch extends Component
{
    public Course $course;

    public Lesson $lesson;

    public bool $enrolled = false;

    public function mount(Course $course, ?Lesson $lesson = null): void
    {
        abort_unless($course->isPublished(), 404);

        $this->course = $course;
        $this->enrolled = auth()->user()->isEnrolledIn($course);

        // Default to the first lesson if none specified.
        $lesson ??= $course->lessons()->orderBy('order')->firstOrFail();

        // Lesson must belong to this course.
        abort_unless($lesson->course_id === $course->id, 404);

        $this->authorizeLesson($lesson);
        $this->lesson = $lesson;

        $this->touchProgress();
    }

    /**
     * A lesson is watchable if it is a free preview, or the user is enrolled.
     */
    protected function authorizeLesson(Lesson $lesson): void
    {
        abort_unless($lesson->is_preview || $this->enrolled, 403);
    }

    public function selectLesson(Lesson $lesson): void
    {
        abort_unless($lesson->course_id === $this->course->id, 404);
        $this->authorizeLesson($lesson);

        $this->lesson = $lesson;
        $this->touchProgress();
    }

    /**
     * Record that the user opened this lesson (creates a progress row).
     */
    protected function touchProgress(): void
    {
        LessonProgress::firstOrCreate([
            'user_id' => auth()->id(),
            'lesson_id' => $this->lesson->id,
        ]);
    }

    public function markComplete(): void
    {
        LessonProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $this->lesson->id],
            ['completed_at' => now()],
        );
    }

    /**
     * The signed/embeddable player URL for the current lesson.
     */
    public function getPlayerUrlProperty(BunnySignedUrlService $bunny): string
    {
        return match ($this->lesson->source) {
            LessonSource::Bunny => $bunny->embedUrl($this->lesson),
            LessonSource::Youtube => "https://www.youtube-nocookie.com/embed/{$this->lesson->video_id}?rel=0&modestbranding=1",
        };
    }

    public function render(): View
    {
        $lessons = $this->course->lessons()->orderBy('order')->get();

        $completedIds = LessonProgress::where('user_id', auth()->id())
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->whereNotNull('completed_at')
            ->pluck('lesson_id')
            ->all();

        $index = $lessons->search(fn ($l) => $l->id === $this->lesson->id);

        return view('livewire.pages.watch', [
            'lessons' => $lessons,
            'completedIds' => $completedIds,
            'previous' => $index > 0 ? $lessons[$index - 1] : null,
            'next' => $index < $lessons->count() - 1 ? $lessons[$index + 1] : null,
        ]);
    }
}
