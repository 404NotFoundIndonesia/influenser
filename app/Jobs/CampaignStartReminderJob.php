<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Notifications\CampaignStartReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class CampaignStartReminderJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $campaigns = Campaign::query()
            ->whereDate('start_date', now()->addDay()->toDateString())
            ->get();

        if ($campaigns->isEmpty()) {
            return;
        }

        $users = User::all();

        foreach ($campaigns as $campaign) {
            Notification::send($users, new CampaignStartReminderNotification($campaign));
        }
    }
}
