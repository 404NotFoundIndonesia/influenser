<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignDeadlineReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class CampaignDeadlineReminderJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $campaigns = Campaign::query()
            ->whereDate('end_date', now()->addDay()->toDateString())
            ->get();

        if ($campaigns->isEmpty()) {
            return;
        }

        $users = User::all();

        foreach ($campaigns as $campaign) {
            Notification::send($users, new CampaignDeadlineReminderNotification($campaign));
        }
    }
}
