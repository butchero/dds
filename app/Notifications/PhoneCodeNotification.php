<?php

namespace App\Notifications;

use App\Models\SiteSetting;
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
        return __(':prefix: codul de confirmare este :code.', [
            'prefix' => SiteSetting::get('sms_sender_prefix'),
            'code' => $this->code,
        ]);
    }
}
