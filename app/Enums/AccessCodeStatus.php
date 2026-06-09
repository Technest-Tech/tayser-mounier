<?php

namespace App\Enums;

enum AccessCodeStatus: string
{
    case Unused = 'unused';
    case Redeemed = 'redeemed';

    public function label(): string
    {
        return match ($this) {
            self::Unused => __('enums.code_status.unused'),
            self::Redeemed => __('enums.code_status.redeemed'),
        };
    }
}
