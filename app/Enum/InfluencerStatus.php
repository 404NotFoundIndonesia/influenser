<?php

namespace App\Enum;

enum InfluencerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Banned = 'banned';

    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value , self::cases());
    }
}
