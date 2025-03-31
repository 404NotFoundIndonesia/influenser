<?php

namespace App\Enum;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
