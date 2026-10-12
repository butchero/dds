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
        $billing = $appointment->billingProfile?->displayName() ?? __('nespecificată');
        $address = $appointment->address?->line() ?? __('nespecificată');

        return (new MailMessage)
            ->subject(__('Programare nouă de la :name', ['name' => $appointment->user?->name]))
            ->line(__('Client: :name', ['name' => $appointment->user?->name ?? __('necunoscut')]))
            ->line(__('Telefon: :phone', ['phone' => $appointment->user?->phone ?? __('nespecificat')]))
            ->line(__('Adresă: :address', ['address' => $address]))
            ->line(__('Facturare: :billing', ['billing' => $billing]))
            ->line(__('Data cerută: :date', ['date' => $appointment->requested_on?->format('d.m.Y') ?? __('nespecificată')]))
            ->line(__('Echipament: :equipment', ['equipment' => $appointment->equipment?->name ?? __('nespecificat')]))
            ->line(__('Observații: :note', ['note' => $appointment->note ?: __('fără')]));
    }
}
