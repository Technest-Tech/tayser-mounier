<?php

namespace App\Enums;

enum LessonSource: string
{
    case Bunny = 'bunny';
    case Youtube = 'youtube';

    public function label(): string
    {
        return match ($this) {
            self::Bunny => __('enums.lesson_source.bunny'),
            self::Youtube => __('enums.lesson_source.youtube'),
        };
    }
}
