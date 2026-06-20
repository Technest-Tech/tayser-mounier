<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonFileController extends Controller
{
    /**
     * Stream the lesson's voice file inline so it plays in the audio element.
     */
    public function audio(Course $course, Lesson $lesson): StreamedResponse
    {
        $path = $this->resolvePath($course, $lesson, $lesson->audio_path);

        return Storage::disk('local')->response($path, $this->fileName($lesson, $path), [
            'Content-Disposition' => 'inline; filename="'.$this->fileName($lesson, $path).'"',
        ]);
    }

    /**
     * Stream the lesson's PDF inline so it previews in the embedded viewer.
     */
    public function pdf(Course $course, Lesson $lesson): StreamedResponse
    {
        $path = $this->resolvePath($course, $lesson, $lesson->pdf_path);

        return Storage::disk('local')->response($path, $this->fileName($lesson, $path), [
            'Content-Disposition' => 'inline; filename="'.$this->fileName($lesson, $path).'"',
        ]);
    }

    /**
     * Validate the request and that the viewer is allowed to access this lesson,
     * mirroring the rules enforced by the Watch page.
     */
    protected function resolvePath(Course $course, Lesson $lesson, ?string $path): string
    {
        abort_unless($course->isPublished(), 404);
        abort_unless($lesson->course_id === $course->id, 404);

        // Free course, free preview lesson, or the user is enrolled.
        abort_unless(
            $course->is_free || $lesson->is_preview || auth()->user()?->isEnrolledIn($course),
            403,
        );

        abort_if(blank($path), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return $path;
    }

    protected function fileName(Lesson $lesson, string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        return Str::slug($lesson->title).($ext ? '.'.$ext : '');
    }
}
