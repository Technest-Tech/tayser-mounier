<?php

namespace Tests\Feature;

use App\Actions\GenerateCodeBatchAction;
use App\Actions\RedeemAccessCodeAction;
use App\Enums\AccessCodeStatus;
use App\Exceptions\AccessCodeException;
use App\Models\AccessCode;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessCodeTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->course = Course::factory()->paid()->create();
        $this->student = User::factory()->create();
    }

    public function test_it_generates_single_use_hashed_codes(): void
    {
        $result = app(GenerateCodeBatchAction::class)->execute($this->course, 5);

        $this->assertCount(5, $result['codes']);
        $this->assertSame(5, AccessCode::where('batch_id', $result['batch_id'])->count());

        $first = AccessCode::where('batch_id', $result['batch_id'])->first();
        $this->assertStringNotContainsString($result['codes'][0], $first->code_hash);
        $this->assertSame(AccessCode::hashCode($result['codes'][0]), $first->code_hash);
    }

    public function test_it_redeems_a_valid_code_and_enrolls_the_student(): void
    {
        $code = app(GenerateCodeBatchAction::class)->execute($this->course, 1)['codes'][0];

        $enrollment = app(RedeemAccessCodeAction::class)->execute($this->student, $code, $this->course);

        $this->assertSame($this->course->id, $enrollment->course_id);
        $this->assertTrue($this->student->fresh()->isEnrolledIn($this->course));
        $this->assertSame(
            AccessCodeStatus::Redeemed,
            AccessCode::where('code_hash', AccessCode::hashCode($code))->first()->status,
        );
    }

    public function test_it_rejects_reusing_a_redeemed_code(): void
    {
        $code = app(GenerateCodeBatchAction::class)->execute($this->course, 1)['codes'][0];
        app(RedeemAccessCodeAction::class)->execute($this->student, $code, $this->course);

        $this->expectException(AccessCodeException::class);
        app(RedeemAccessCodeAction::class)->execute(User::factory()->create(), $code, $this->course);
    }

    public function test_it_rejects_an_invalid_code(): void
    {
        $this->expectException(AccessCodeException::class);
        app(RedeemAccessCodeAction::class)->execute($this->student, 'XXXX-YYYY-ZZZZ', $this->course);
    }

    public function test_it_rejects_a_code_for_another_course(): void
    {
        $otherCourse = Course::factory()->paid()->create();
        $code = app(GenerateCodeBatchAction::class)->execute($otherCourse, 1)['codes'][0];

        $this->expectException(AccessCodeException::class);
        app(RedeemAccessCodeAction::class)->execute($this->student, $code, $this->course);
    }

    public function test_it_rejects_an_expired_code(): void
    {
        $code = app(GenerateCodeBatchAction::class)
            ->execute($this->course, 1, now()->subDay())['codes'][0];

        $this->expectException(AccessCodeException::class);
        app(RedeemAccessCodeAction::class)->execute($this->student, $code, $this->course);
    }
}
