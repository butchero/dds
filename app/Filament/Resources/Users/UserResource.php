<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Address;
use App\Models\User;
use App\Support\Counties;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Clienți');
    }

    public static function getModelLabel(): string
    {
        return __('client');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clienți');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Clienți');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Nume'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('E-mail'))
                    ->email()
                    ->required(fn (Get $get): bool => $get('role') === 'admin')
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('Telefon'))
                    ->tel(),
                Select::make('role')
                    ->label(__('Rol'))
                    ->options(self::roles())
                    ->required()
                    ->default('client')
                    ->live(),
                DateTimePicker::make('email_verified_at')
                    ->label(__('E-mail verificat la')),
                TextInput::make('password')
                    ->label(__('Parolă'))
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation, Get $get): bool => $operation === 'create' && ($get('role') === 'admin' || filled($get('email')))),
                Section::make(__('Adrese'))
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->schema([
                        Repeater::make('addresses')
                            ->relationship()
                            ->schema(self::addressFields())
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Echipamente'))
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->schema([
                        Repeater::make('equipment')
                            ->relationship()
                            ->schema(self::equipmentFields())
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Facturare'))
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->schema([
                        Repeater::make('billingProfiles')
                            ->relationship()
                            ->schema(self::billingFields())
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int, TextInput|Select>
     */
    public static function addressFields(): array
    {
        return [
            TextInput::make('label')->label(__('Etichetă')),
            Select::make('county')->label(__('Județ'))->options(Counties::options())->required()->searchable(),
            TextInput::make('city')->label(__('Localitate'))->required(),
            TextInput::make('street')->label(__('Stradă'))->required(),
            TextInput::make('street_number')->label(__('Număr'))->required(),
            TextInput::make('postal_code')->label(__('Cod poștal')),
            TextInput::make('details')->label(__('Detalii'))->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, TextInput|Select|DatePicker|Toggle>
     */
    public static function equipmentFields(): array
    {
        return [
            TextInput::make('name')->label(__('Nume'))->required(),
            TextInput::make('type')->label(__('Tip')),
            Select::make('address_id')
                ->label(__('Adresă'))
                ->options(function (mixed $livewire): array {
                    $owner = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : null;

                    return $owner instanceof User ? self::addressOptions($owner) : [];
                })
                ->searchable(),
            Select::make('interval_months')
                ->label(__('Interval (luni)'))
                ->options([12 => '12', 24 => '24'])
                ->default(24)
                ->required(),
            DatePicker::make('last_revision_on')->label(__('Ultima revizie')),
            Toggle::make('notify_email')->label(__('Notificare pe email')),
            Toggle::make('notify_sms')->label(__('Notificare pe SMS')),
        ];
    }

    /**
     * @return array<int, TextInput|Select|Toggle>
     */
    public static function billingFields(): array
    {
        return [
            Select::make('type')
                ->label(__('Tip'))
                ->options([
                    'individual' => __('Persoană fizică'),
                    'company' => __('Persoană juridică'),
                ])
                ->required()
                ->live(),
            TextInput::make('last_name')->label(__('Nume'))->visible(fn (Get $get): bool => $get('type') === 'individual')->required(fn (Get $get): bool => $get('type') === 'individual'),
            TextInput::make('first_name')->label(__('Prenume'))->visible(fn (Get $get): bool => $get('type') === 'individual')->required(fn (Get $get): bool => $get('type') === 'individual'),
            TextInput::make('company_name')->label(__('Denumire'))->visible(fn (Get $get): bool => $get('type') === 'company')->required(fn (Get $get): bool => $get('type') === 'company'),
            TextInput::make('cui')->label(__('CUI'))->visible(fn (Get $get): bool => $get('type') === 'company')->required(fn (Get $get): bool => $get('type') === 'company'),
            TextInput::make('registration_number')->label(__('Nr. Reg. Com.'))->visible(fn (Get $get): bool => $get('type') === 'company')->required(fn (Get $get): bool => $get('type') === 'company'),
            Toggle::make('is_vat_payer')->label(__('Plătitor de TVA'))->visible(fn (Get $get): bool => $get('type') === 'company'),
            Select::make('county')->label(__('Județ'))->options(Counties::options())->required()->searchable(),
            TextInput::make('city')->label(__('Localitate'))->required(),
            TextInput::make('street')->label(__('Stradă'))->required(),
            TextInput::make('street_number')->label(__('Număr'))->required(),
            TextInput::make('postal_code')->label(__('Cod poștal')),
            TextInput::make('bank')->label(__('Bancă'))->visible(fn (Get $get): bool => $get('type') === 'company'),
            TextInput::make('iban')->label(__('IBAN'))->visible(fn (Get $get): bool => $get('type') === 'company'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function addressOptions(?User $record): array
    {
        if (! $record) {
            return [];
        }

        return $record->addresses()
            ->get()
            ->mapWithKeys(fn (Address $address): array => [$address->id => $address->line()])
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nume'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('E-mail'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Telefon'))
                    ->searchable(),
                TextColumn::make('role')
                    ->label(__('Rol'))
                    ->formatStateUsing(fn (string $state): string => self::roles()[$state] ?? $state)
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(__('E-mail verificat la'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Creat la'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Actualizat la'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function roles(): array
    {
        return [
            'admin' => __('Administrator'),
            'client' => __('Client'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
