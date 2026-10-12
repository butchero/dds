<?php

namespace App\Notifications;

use App\Support\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $department,
        public string $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $department = ContactInquiry::departments()[$this->department] ?? $this->department;

        return (new MailMessage)
            ->subject(__('Mesaj de contact - :department', ['department' => $department]))
            ->replyTo($this->email, str_replace(["\r", "\n"], '', $this->name))
            ->line(__('Nume: :name', ['name' => $this->name]))
            ->line(__('E-mail: :email', ['email' => $this->email]))
            ->line(__('Telefon: :phone', ['phone' => $this->phone]))
            ->line(__('Departament: :department', ['department' => $department]))
            ->line(__('Mesaj: :message', ['message' => $this->message]));
    }
}
