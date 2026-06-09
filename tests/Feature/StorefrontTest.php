<?php

namespace Tests\Feature;

use App\Actions\GenerateCodeBatchAction;
use App\Livewire\Pages\CourseShow;
use App\Livewire\Pages\Watch;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $course = Course::factory()->create();
        Lesson::factory()->for($course)->create();

        $this->get('/')->assertSuccessful();
        $this->get('/courses')->assertSuccessful();
        $this->get('/courses/'.$course->slug)->assertSuccessful();
    }

    public function test_draft_course_is_hidden(): void
    {
        $course = Course::factory()->draft()->create();

        $this->get('/courses/'.$course->slug)->assertNotFound();
    }

    public function test_user_can_enroll_in_a_free_course(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->free()->create();
        Lesson::factory()->for($course)->create(['order' => 1]);

        Livewire::actingAs($user)
            ->test(CourseShow::class, ['course' => $course])
            ->call('enrollFree');

        $this->assertTrue($user->fresh()->isEnrolledIn($course));
    }

    public function test_user_can_unlock_a_paid_course_with_a_valid_code(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->paid()->create();
        Lesson::factory()->for($course)->create(['order' => 1]);
        $code = app(GenerateCodeBatchAction::class)->execute($course, 1)['codes'][0];

        Livewire::actingAs($user)
            ->test(CourseShow::class, ['course' => $course])
            ->set('code', $code)
            ->call('redeem');

        $this->assertTrue($user->fresh()->isEnrolledIn($course));
    }

    public function test_bad_code_shows_validation_error(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->paid()->create();

        Livewire::actingAs($user)
            ->test(CourseShow::class, ['course' => $course])
            ->set('code', 'BAD-CODE-HERE')
            ->call('redeem')
            ->assertHasErrors('code');
    }

    public function test_non_preview_lesson_blocked_without_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->paid()->create();
        $lesson = Lesson::factory()->for($course)->create(['is_preview' => false, 'order' => 1]);

        Livewire::actingAs($user)
            ->test(Watch::class, ['course' => $course, 'lesson' => $lesson])
            ->assertForbidden();
    }

    public function test_preview_lesson_watchable_without_enrollment(): void
    {
        $user = User::factory()->create();
        $course = Course::factory()->paid()->create();
        $lesson = Lesson::factory()->preview()->for($course)->create(['order' => 1]);

        Livewire::actingAs($user)
            ->test(Watch::class, ['course' => $course, 'lesson' => $lesson])
            ->assertSuccessful();
    }
}
