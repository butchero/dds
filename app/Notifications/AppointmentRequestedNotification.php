<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appointment = $this->appointment->loadMissing(['user', 'address', 'billingProfile', 'equipment']);
        $billing = $appointment->billingProfile?->displayName() ?? 'nespecificată';
        $address = $appointment->address?->line() ?? 'nespecificată';

        return (new MailMessage)
            ->subject('Programare nouă de la '.$appointment->user?->name)
            ->line('Client: '.($appointment->user?->name ?? 'necunoscut'))
            ->line('Telefon: '.($appointment->user?->phone ?? 'nespecificat'))
            ->line('Adresă: '.$address)
            ->line('Facturare: '.$billing)
            ->line('Data cerută: '.($appointment->requested_on?->format('d.m.Y') ?? 'nespecificată'))
            ->line('Echipament: '.($appointment->equipment?->name ?? 'nespecificat'))
            ->line('Observații: '.($appointment->note ?: 'fără'));
    }
}
