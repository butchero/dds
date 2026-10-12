<?php

namespace App\Notifications;

use App\Models\Equipment;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionDueNotification extends Notification
{
    use Queueable;

    public function __construct(public Equipment $equipment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->equipment->notify_email && filled($notifiable->email) && $notifiable->hasVerifiedEmail()) {
            $channels[] = 'mail';
        }

        if ($this->equipment->notify_sms && filled($notifiable->routeNotificationFor('sms', $this))) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = $this->equipment->next_revision_on?->format('d.m.Y');

        return (new MailMessage)
            ->subject(__('Se apropie revizia pentru :equipment', ['equipment' => $this->equipment->name]))
            ->line(__('Urmatoarea revizie pentru :equipment este la :date.', [
                'equipment' => $this->equipment->name,
                'date' => $date,
            ]))
            ->line(__('Puteti cere o programare din contul de client.'));
    }

    public function toSms(object $notifiable): string
    {
        return __(':prefix: revizia pentru :equipment este la :date.', [
            'prefix' => SiteSetting::get('sms_sender_prefix'),
            'equipment' => $this->equipment->name,
            'date' => $this->equipment->next_revision_on?->format('d.m.Y'),
        ]);
    }
}
