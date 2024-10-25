<?php

namespace App\Enums;

enum FilterGroupingEnum : string
{
    case USER = 'user';
    case TAG = 'tag';
    case PRIORITY = 'priority';

    public static function isSelected(string|null $value, self $case): bool
    {
        return $value === $case->value;
    }
}
