<?php

namespace App\Livewire\Pages;

use App\Enums\LessonSource;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonQuizAttempt;
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

    /**
     * Student's chosen option per question: [question_id => option_id].
     *
     * @var array<int, int>
     */
    public array $answers = [];

    /** Whether the current lesson's quiz has been submitted (results showing). */
    public bool $quizSubmitted = false;

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
        $this->loadQuizState();
    }

    /**
     * A lesson is watchable if the course is free, it is a preview, or the user is enrolled.
     */
    protected function authorizeLesson(Lesson $lesson): void
    {
        abort_unless($this->course->is_free || $lesson->is_preview || $this->enrolled, 403);
    }

    public function selectLesson(Lesson $lesson): void
    {
        abort_unless($lesson->course_id === $this->course->id, 404);
        $this->authorizeLesson($lesson);

        $this->lesson = $lesson;
        $this->touchProgress();
        $this->loadQuizState();
    }

    /**
     * Reset the quiz UI for the current lesson, restoring the most recent
     * attempt's answers so the student sees their results when they return.
     */
    protected function loadQuizState(): void
    {
        $this->answers = [];
        $this->quizSubmitted = false;

        $attempt = LessonQuizAttempt::where('user_id', auth()->id())
            ->where('lesson_id', $this->lesson->id)
            ->latest()
            ->first();

        if ($attempt) {
            $this->answers = collect($attempt->answers)
                ->mapWithKeys(fn ($optionId, $questionId) => [(int) $questionId => (int) $optionId])
                ->all();
            $this->quizSubmitted = true;
        }
    }

    /**
     * Pick an answer for a question (ignored once the quiz is submitted).
     */
    public function chooseOption(int $questionId, int $optionId): void
    {
        if ($this->quizSubmitted) {
            return;
        }

        $this->answers[$questionId] = $optionId;
    }

    /**
     * Grade the answers, store the attempt, and switch to the results view.
     */
    public function submitQuiz(): void
    {
        $questions = $this->lesson->questions()->with('options')->get();

        if ($questions->isEmpty()) {
            return;
        }

        // Require every question to be answered before grading.
        foreach ($questions as $question) {
            abort_unless(isset($this->answers[$question->id]), 422);
        }

        $score = $questions->reduce(function (int $carry, $question) {
            $correct = $question->options->firstWhere('is_correct', true);

            return $carry + ($correct && (int) $this->answers[$question->id] === $correct->id ? 1 : 0);
        }, 0);

        LessonQuizAttempt::create([
            'user_id' => auth()->id(),
            'lesson_id' => $this->lesson->id,
            'score' => $score,
            'total' => $questions->count(),
            'answers' => $this->answers,
            'completed_at' => now(),
        ]);

        $this->quizSubmitted = true;
    }

    /**
     * Clear the answers and let the student take the quiz again.
     */
    public function retakeQuiz(): void
    {
        $this->answers = [];
        $this->quizSubmitted = false;
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

        $questions = $this->lesson->questions()->with('options')->get();

        $quizScore = $this->quizSubmitted
            ? $questions->reduce(function (int $carry, $question) {
                $correct = $question->options->firstWhere('is_correct', true);
                $chosen = $this->answers[$question->id] ?? null;

                return $carry + ($correct && (int) $chosen === $correct->id ? 1 : 0);
            }, 0)
            : null;

        return view('livewire.pages.watch', [
            'lessons' => $lessons,
            'completedIds' => $completedIds,
            'previous' => $index > 0 ? $lessons[$index - 1] : null,
            'next' => $index < $lessons->count() - 1 ? $lessons[$index + 1] : null,
            'questions' => $questions,
            'quizScore' => $quizScore,
        ]);
    }
}
