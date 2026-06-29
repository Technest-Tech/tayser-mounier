<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonQuizAttempt;
use Illuminate\Contracts\View\View;

class CertificateController extends Controller
{
    /**
     * Render the printable completion certificate for a lesson quiz.
     *
     * Only available to a signed-in student who has access to the lesson, where
     * the lesson actually has questions and the student has a recorded attempt.
     */
    public function show(Course $course, Lesson $lesson): View
    {
        $user = auth()->user();

        abort_unless($user !== null, 403);
        abort_unless($course->isPublished(), 404);
        abort_unless($lesson->course_id === $course->id, 404);

        // Same access rules as the lesson player: free course, enrolled, or a
        // free preview lesson.
        abort_unless(
            $lesson->is_preview || $course->is_free || $user->isEnrolledIn($course),
            403,
        );

        // A certificate only exists for lessons that carry a quiz.
        abort_unless($lesson->questions()->exists(), 404);

        $attempt = LessonQuizAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->latest()
            ->first();

        abort_unless($attempt !== null, 404);

        $percentage = $attempt->percentage();

        return view('certificate', [
            'course' => $course,
            'lesson' => $lesson,
            'attempt' => $attempt,
            'user' => $user,
            'percentage' => $percentage,
            'grade' => $this->grade($percentage),
        ]);
    }

    /**
     * Map a percentage score to a localized grade label.
     */
    protected function grade(int $percentage): string
    {
        return match (true) {
            $percentage >= 90 => __('certificate.grade_excellent'),
            $percentage >= 80 => __('certificate.grade_verygood'),
            $percentage >= 70 => __('certificate.grade_good'),
            $percentage >= 50 => __('certificate.grade_pass'),
            default => __('certificate.grade_fail'),
        };
    }
}
