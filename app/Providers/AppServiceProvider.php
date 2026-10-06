<?php

namespace App\Providers;

use App\Models\User;
use App\Notifications\Channels\SmsChannel;
use App\Support\SiteCatalog;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.site-layout', function (ViewContract $view): void {
            $view->with('site', SiteCatalog::shared());
        });

        Notification::extend('sms', fn () => new SmsChannel);

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirmă adresa de e-mail')
                ->line('Apasă butonul ca să confirmi adresa de e-mail a contului DDS.')
                ->action('Confirmă e-mailul', $url)
                ->line('Dacă nu ai cerut tu contul, poți ignora acest mesaj.');
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
        });

        $labels = [
            'name' => 'Nume',
            'title' => 'Titlu',
            'slug' => 'Adresă',
            'email' => 'E-mail',
            'phone' => 'Telefon',
            'password' => 'Parolă',
            'role' => 'Rol',
            'sort' => 'Ordine',
            'image' => 'Imagine',
            'link' => 'Link',
            'url' => 'Adresă',
            'label' => 'Etichetă',
            'excerpt' => 'Rezumat',
            'description' => 'Descriere',
            'manufacturer' => 'Producător',
            'type' => 'Tip',
            'note' => 'Observații',
            'status' => 'Stare',
            'blocks' => 'Blocuri',
            'is_published' => 'Publicat',
            'show_in_sidebar' => 'În meniul din stânga',
            'menu_position' => 'Poziție meniu',
            'published_at' => 'Data publicării',
            'parent_id' => 'Categorie părinte',
            'user_id' => 'Client',
            'equipment_id' => 'Echipament',
            'product_category_id' => 'Categorie',
            'service_category_id' => 'Categorie',
            'last_revision_on' => 'Ultima revizie',
            'next_revision_on' => 'Următoarea revizie',
            'notified_on' => 'Notificat la',
            'interval_months' => 'Interval (luni)',
            'requested_on' => 'Data cerută',
            'email_verified_at' => 'E-mail verificat la',
            'created_at' => 'Creat la',
            'updated_at' => 'Actualizat la',
        ];

        Field::configureUsing(function (Field $field) use ($labels): void {
            $name = $field->getName();

            if (isset($labels[$name])) {
                $field->label($labels[$name]);
            }
        });

        Column::configureUsing(function (Column $column) use ($labels): void {
            $name = $column->getName();

            if (isset($labels[$name])) {
                $column->label($labels[$name]);
            }
        });
    }
}
