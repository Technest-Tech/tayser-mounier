<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Admin => __('enums.role.admin'),
            self::Student => __('enums.role.student'),
        };
    }
}
