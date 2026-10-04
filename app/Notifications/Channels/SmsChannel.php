<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        $phone = $notifiable->routeNotificationFor('sms', $notification);

        if (blank($phone) || blank($message)) {
            return;
        }

        if (config('services.sms.driver') !== 'http' || blank(config('services.sms.url'))) {
            Log::info('SMS revizie', ['phone' => $phone, 'message' => $message]);

            return;
        }

        Http::asForm()->post(config('services.sms.url'), [
            'to' => $phone,
            'from' => config('services.sms.from'),
            'message' => $message,
        ]);
    }
}
