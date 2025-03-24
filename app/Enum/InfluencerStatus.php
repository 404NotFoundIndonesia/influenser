<?php

namespace App\Enum;

enum InfluencerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Banned = 'banned';
}
