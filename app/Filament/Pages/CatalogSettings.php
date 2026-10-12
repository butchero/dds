<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class CatalogSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 40;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Setări catalog');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Setări catalog');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Catalog');
    }

    public function mount(): void
    {
        $this->form->fill([
            'vat_rate' => SiteSetting::get('vat_rate'),
            'prices_include_vat' => SiteSetting::get('prices_include_vat'),
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
                TextInput::make('vat_rate')
                    ->label(__('Cotă TVA (%)'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01),
                Toggle::make('prices_include_vat')
                    ->label(__('Prețurile introduse includ TVA'))
                    ->helperText(__('Dacă e oprit, prețul introdus la produs este fără TVA.')),
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

        SiteSetting::set('vat_rate', $state['vat_rate']);
        SiteSetting::set('prices_include_vat', $state['prices_include_vat']);

        Notification::make()
            ->title(__('Setările au fost salvate.'))
            ->success()
            ->send();
    }
}
