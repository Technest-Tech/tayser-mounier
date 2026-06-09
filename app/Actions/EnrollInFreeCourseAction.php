<?php

namespace App\Actions;

use App\Enums\EnrollmentSource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

/**
 * Enrolls a user in a free course. Idempotent — returns the existing enrollment
 * if the user is already enrolled.
 */
class EnrollInFreeCourseAction
{
    public function execute(User $user, Course $course): Enrollment
    {
        return Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['source' => EnrollmentSource::Free],
        );
    }
}
