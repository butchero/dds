<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class FirmSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 40;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Date firmă');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Date firmă');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Clienți');
    }

    public function mount(): void
    {
        $this->form->fill([
            'company_email' => SiteSetting::get('company_email'),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_email')
                    ->label(__('E-mail firmă'))
                    ->email()
                    ->required()
                    ->helperText(__('Primește un mesaj la fiecare programare nouă.')),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('Salvează'))
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::set('company_email', $state['company_email']);

        Notification::make()
            ->title(__('Adresa a fost salvată.'))
            ->success()
            ->send();
    }
}
