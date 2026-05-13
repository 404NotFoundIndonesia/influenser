<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignStartReminderNotification extends Notification
{
    public function __construct(
        public readonly Campaign $campaign,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder: Campaign "'.$this->campaign->name.'" starts tomorrow')
            ->markdown('mail.campaign-start-reminder', [
                'campaign' => $this->campaign,
                'notifiable' => $notifiable,
            ]);
    }
}
