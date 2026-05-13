<?php

namespace App\Notifications;

use App\Models\Campaign;
use App\Models\KeyOpinionLeader;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignBriefNotification extends Notification
{
    public function __construct(
        public readonly Campaign $campaign,
        public readonly KeyOpinionLeader $kol,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Campaign Brief: '.$this->campaign->name)
            ->markdown('mail.campaign-brief', [
                'campaign' => $this->campaign,
                'kol' => $this->kol,
                'notifiable' => $notifiable,
            ]);
    }
}
