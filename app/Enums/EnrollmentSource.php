<?php

namespace App\Enums;

enum EnrollmentSource: string
{
    case Free = 'free';
    case Code = 'code';

    public function label(): string
    {
        return match ($this) {
            self::Free => __('enums.enrollment_source.free'),
            self::Code => __('enums.enrollment_source.code'),
        };
    }
}
