<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\ContactOffice;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class SiteConfiguration extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 50;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Setări site');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Setări site');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Conținut');
    }

    public function mount(): void
    {
        $shareImage = SiteSetting::get('share_image');

        $this->form->fill([
            'home_heading' => SiteSetting::get('home_heading'),
            'title_suffix' => SiteSetting::get('title_suffix'),
            'founded_year' => SiteSetting::get('founded_year'),
            'copyright_start_year' => SiteSetting::get('copyright_start_year'),
            'brand_primary' => SiteSetting::get('brand_primary'),
            'currency_suffix' => SiteSetting::get('currency_suffix'),
            'sms_sender_prefix' => SiteSetting::get('sms_sender_prefix'),
            'meta_description' => SiteSetting::get('meta_description'),
            'share_image' => filled($shareImage) ? $shareImage : null,
            'company_email' => SiteSetting::get('company_email'),
            'contact_company' => SiteSetting::get('contact_company'),
            'contact_address' => SiteSetting::get('contact_address'),
            'contact_city' => SiteSetting::get('contact_city'),
            'contact_email' => SiteSetting::get('contact_email'),
            'contact_phone' => SiteSetting::get('contact_phone'),
            'contact_fax' => SiteSetting::get('contact_fax'),
            'contact_mobile' => SiteSetting::get('contact_mobile'),
            'contact_mobile_alt' => SiteSetting::get('contact_mobile_alt'),
            'contact_latitude' => SiteSetting::get('contact_latitude'),
            'contact_longitude' => SiteSetting::get('contact_longitude'),
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
                TextInput::make('home_heading')
                    ->label(__('Titlul primei pagini'))
                    ->required()
                    ->helperText(__('Apare doar ca titlu principal pe prima pagină.')),
                TextInput::make('title_suffix')
                    ->label(__('Numele brandului'))
                    ->required()
                    ->helperText(__('Sufixul din titlul paginii, numele din admin și din mesajele către clienți.')),
                TextInput::make('founded_year')
                    ->label(__('Anul înființării'))
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(now()->year)
                    ->helperText(__('Apare pe prima pagină, în textul despre firmă.')),
                TextInput::make('copyright_start_year')
                    ->label(__('Anul de început'))
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(now()->year)
                    ->helperText(__('Primul an din copyrightul din subsol. Anul curent se adaugă singur.')),
                TextInput::make('brand_primary')
                    ->label(__('Culoarea brandului'))
                    ->required()
                    ->regex('/^#[0-9A-Fa-f]{6}$/')
                    ->helperText(__('Cod hex, de exemplu #fe5013. Se aplică în panoul de administrare.')),
                TextInput::make('currency_suffix')
                    ->label(__('Sufixul monedei'))
                    ->required()
                    ->maxLength(12)
                    ->helperText(__('Apare după prețuri în catalog, de exemplu lei.')),
                TextInput::make('sms_sender_prefix')
                    ->label(__('Prefix SMS'))
                    ->required()
                    ->maxLength(20)
                    ->helperText(__('Apare la începutul mesajelor SMS trimise clienților.')),
                Textarea::make('meta_description')
                    ->label(__('Descrierea implicită'))
                    ->required()
                    ->rows(3)
                    ->helperText(__('Apare când pagina nu are un rezumat sau o descriere proprie.')),
                FileUpload::make('share_image')
                    ->label(__('Imaginea de share'))
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png'])
                    ->disk('public')
                    ->directory('site')
                    ->visibility('public')
                    ->helperText(__('JPEG sau PNG. Apare când pagina nu are o poză proprie.')),
                Section::make(__('Contact'))
                    ->schema([
                        TextInput::make('contact_company')
                            ->label(__('Firmă'))
                            ->required()
                            ->maxLength(160)
                            ->columnSpanFull(),
                        TextInput::make('contact_address')
                            ->label(__('Adresă'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('contact_city')
                            ->label(__('Localitate'))
                            ->required()
                            ->maxLength(120),
                        TextInput::make('contact_email')
                            ->label(__('E-mail afișat'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('contact_phone')
                            ->label(__('Telefon'))
                            ->required()
                            ->maxLength(40),
                        TextInput::make('contact_fax')
                            ->label(__('Fax'))
                            ->maxLength(40),
                        TextInput::make('contact_mobile')
                            ->label(__('Mobil'))
                            ->maxLength(40),
                        TextInput::make('contact_mobile_alt')
                            ->label(__('Mobil secundar'))
                            ->maxLength(40),
                        TextInput::make('contact_latitude')
                            ->label(__('Latitudine'))
                            ->required()
                            ->numeric()
                            ->minValue(-90)
                            ->maxValue(90)
                            ->helperText(__('Coordonata folosită de harta de pe pagina de contact.')),
                        TextInput::make('contact_longitude')
                            ->label(__('Longitudine'))
                            ->required()
                            ->numeric()
                            ->minValue(-180)
                            ->maxValue(180),
                        TextInput::make('company_email')
                            ->label(__('E-mail pentru mesaje'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText(__('Aici ajung mesajele din formular și programările noi.')),
                    ])
                    ->columns(2),
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

        SiteSetting::set('home_heading', $state['home_heading']);
        SiteSetting::set('title_suffix', $state['title_suffix']);
        SiteSetting::set('founded_year', $state['founded_year']);
        SiteSetting::set('copyright_start_year', $state['copyright_start_year']);
        SiteSetting::set('brand_primary', $state['brand_primary']);
        SiteSetting::set('currency_suffix', $state['currency_suffix']);
        SiteSetting::set('sms_sender_prefix', $state['sms_sender_prefix']);
        SiteSetting::set('meta_description', $state['meta_description']);
        SiteSetting::set('share_image', self::storedImage($state['share_image'] ?? null));
        SiteSetting::set('company_email', $state['company_email']);
        SiteSetting::set('contact_company', $state['contact_company']);
        SiteSetting::set('contact_address', $state['contact_address']);
        SiteSetting::set('contact_city', $state['contact_city']);
        SiteSetting::set('contact_email', $state['contact_email']);
        SiteSetting::set('contact_phone', $state['contact_phone']);
        SiteSetting::set('contact_fax', $state['contact_fax'] ?? '');
        SiteSetting::set('contact_mobile', $state['contact_mobile'] ?? '');
        SiteSetting::set('contact_mobile_alt', $state['contact_mobile_alt'] ?? '');
        SiteSetting::set('contact_latitude', ContactOffice::storedCoordinate($state['contact_latitude']));
        SiteSetting::set('contact_longitude', ContactOffice::storedCoordinate($state['contact_longitude']));

        Notification::make()
            ->title(__('Setările au fost salvate.'))
            ->success()
            ->send();
    }

    private static function storedImage(mixed $image): string
    {
        if (is_array($image)) {
            $image = $image[0] ?? '';
        }

        return is_string($image) ? $image : '';
    }
}
