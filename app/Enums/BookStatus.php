<?php

namespace App\Enums;

enum BookStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('enums.book_status.draft'),
            self::Published => __('enums.book_status.published'),
        };
    }
}
