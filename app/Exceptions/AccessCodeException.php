<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an access code cannot be redeemed. The message is a translation
 * key resolved for display to the student.
 */
class AccessCodeException extends Exception
{
    public static function invalid(): self
    {
        return new self(__('codes.errors.invalid'));
    }

    public static function expired(): self
    {
        return new self(__('codes.errors.expired'));
    }

    public static function alreadyUsed(): self
    {
        return new self(__('codes.errors.already_used'));
    }

    public static function wrongCourse(): self
    {
        return new self(__('codes.errors.wrong_course'));
    }

    public static function alreadyEnrolled(): self
    {
        return new self(__('codes.errors.already_enrolled'));
    }
}
