<?php

namespace App\Providers;

use App\Notifications\Channels\SmsChannel;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

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
        Notification::extend('sms', fn () => new SmsChannel);

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
