<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('enums.course_status.draft'),
            self::Published => __('enums.course_status.published'),
        };
    }
}
