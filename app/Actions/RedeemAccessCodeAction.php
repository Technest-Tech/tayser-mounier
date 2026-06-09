<?php

namespace App\Actions;

use App\Enums\AccessCodeStatus;
use App\Enums\EnrollmentSource;
use App\Exceptions\AccessCodeException;
use App\Models\AccessCode;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Redeems a single-use access code and enrolls the user in the course.
 *
 * Concurrency-safe: the code row is locked for update inside a transaction so
 * two students cannot redeem the same code at the same time.
 */
class RedeemAccessCodeAction
{
    /**
     * @throws AccessCodeException
     */
    public function execute(User $user, string $plainCode, ?Course $expectedCourse = null): Enrollment
    {
        $hash = AccessCode::hashCode($plainCode);

        return DB::transaction(function () use ($user, $hash, $expectedCourse) {
            /** @var AccessCode|null $code */
            $code = AccessCode::where('code_hash', $hash)
                ->lockForUpdate()
                ->first();

            if ($code === null) {
                throw AccessCodeException::invalid();
            }

            if ($expectedCourse !== null && $code->course_id !== $expectedCourse->id) {
                throw AccessCodeException::wrongCourse();
            }

            if ($code->status === AccessCodeStatus::Redeemed) {
                throw AccessCodeException::alreadyUsed();
            }

            if ($code->isExpired()) {
                throw AccessCodeException::expired();
            }

            // Already enrolled (e.g. via a free path or another code)? Don't burn the code.
            if ($user->isEnrolledIn($code->course)) {
                throw AccessCodeException::alreadyEnrolled();
            }

            $code->update([
                'status' => AccessCodeStatus::Redeemed,
                'redeemed_by' => $user->id,
                'redeemed_at' => now(),
            ]);

            return Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $code->course_id,
                'source' => EnrollmentSource::Code,
            ]);
        });
    }
}
