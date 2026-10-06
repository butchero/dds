<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PhoneCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['sms'];
    }

    public function toSms(object $notifiable): string
    {
        return 'DDS: codul de confirmare este '.$this->code.'.';
    }
}
