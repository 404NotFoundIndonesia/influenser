<?php

namespace App\Enum;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value , self::cases());
    }
}
