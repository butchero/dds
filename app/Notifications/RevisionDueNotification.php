<?php

namespace App\Notifications;

use App\Models\Equipment;
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
        return (new MailMessage)
            ->subject('Se apropie revizia pentru '.$this->equipment->name)
            ->line('Urmatoarea revizie pentru '.$this->equipment->name.' este la '.$this->equipment->next_revision_on?->format('d.m.Y').'.')
            ->line('Puteti cere o programare din contul de client.');
    }

    public function toSms(object $notifiable): string
    {
        return 'DDS: revizia pentru '.$this->equipment->name.' este la '.$this->equipment->next_revision_on?->format('d.m.Y').'.';
    }
}
