<?php

namespace App\Providers;

use App\Models\SiteSetting;
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
                ->subject(__('Confirmă adresa de e-mail'))
                ->line(__('Apasă butonul ca să confirmi adresa de e-mail a contului :brand.', [
                    'brand' => SiteSetting::get('title_suffix'),
                ]))
                ->action(__('Confirmă e-mailul'), $url)
                ->line(__('Dacă nu ai cerut tu contul, poți ignora acest mesaj.'));
        });

        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
        });

        $labels = [
            'name' => __('Nume'),
            'title' => __('Titlu'),
            'slug' => __('Adresă'),
            'email' => __('E-mail'),
            'phone' => __('Telefon'),
            'password' => __('Parolă'),
            'role' => __('Rol'),
            'sort' => __('Ordine'),
            'image' => __('Imagine'),
            'link' => __('Link'),
            'url' => __('Adresă'),
            'label' => __('Etichetă'),
            'excerpt' => __('Rezumat'),
            'description' => __('Descriere'),
            'manufacturer_id' => __('Producător'),
            'logo' => __('Logo'),
            'type' => __('Tip'),
            'note' => __('Observații'),
            'status' => __('Stare'),
            'blocks' => __('Blocuri'),
            'is_published' => __('Publicat'),
            'show_in_sidebar' => __('În meniul din stânga'),
            'menu_position' => __('Poziție meniu'),
            'published_at' => __('Data publicării'),
            'parent_id' => __('Categorie părinte'),
            'user_id' => __('Client'),
            'equipment_id' => __('Echipament'),
            'product_category_id' => __('Categorie'),
            'service_category_id' => __('Categorie'),
            'last_revision_on' => __('Ultima revizie'),
            'next_revision_on' => __('Următoarea revizie'),
            'notified_on' => __('Notificat la'),
            'interval_months' => __('Interval (luni)'),
            'requested_on' => __('Data cerută'),
            'email_verified_at' => __('E-mail verificat la'),
            'created_at' => __('Creat la'),
            'updated_at' => __('Actualizat la'),
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
