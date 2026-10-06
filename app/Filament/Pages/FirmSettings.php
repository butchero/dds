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

class FirmSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Date firmă';

    protected static ?string $title = 'Date firmă';

    protected static string|\UnitEnum|null $navigationGroup = 'Clienți';

    protected static ?int $navigationSort = 40;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

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
                    ->label('E-mail firmă')
                    ->email()
                    ->required()
                    ->helperText('Primește un mesaj la fiecare programare nouă.'),
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
                                ->label('Salvează')
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
            ->title('Adresa a fost salvată.')
            ->success()
            ->send();
    }
}
