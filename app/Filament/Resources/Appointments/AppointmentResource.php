<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\ManageAppointments;
use App\Models\Address;
use App\Models\Appointment;
use App\Models\BillingProfile;
use App\Models\Equipment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Programări';

    protected static ?string $modelLabel = 'programare';

    protected static ?string $pluralModelLabel = 'Programări';

    protected static string|\UnitEnum|null $navigationGroup = 'Clienți';

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Client')
                    ->relationship('user', 'name')
                    ->required()
                    ->live(),
                Select::make('address_id')
                    ->label('Adresă')
                    ->options(fn (Get $get): array => Address::query()
                        ->where('user_id', $get('user_id'))
                        ->get()
                        ->mapWithKeys(fn (Address $address): array => [$address->id => $address->line()])
                        ->all())
                    ->searchable()
                    ->live(),
                Select::make('billing_profile_id')
                    ->label('Facturare')
                    ->options(fn (Get $get): array => BillingProfile::query()
                        ->where('user_id', $get('user_id'))
                        ->get()
                        ->mapWithKeys(fn (BillingProfile $profile): array => [$profile->id => $profile->displayName()])
                        ->all())
                    ->searchable(),
                Select::make('equipment_id')
                    ->label('Echipament')
                    ->options(fn (Get $get): array => Equipment::query()
                        ->where('user_id', $get('user_id'))
                        ->when($get('address_id'), fn ($query, $addressId) => $query->where('address_id', $addressId))
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
                DatePicker::make('requested_on')
                    ->label('Data solicitată'),
                Textarea::make('note')
                    ->label('Notă')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Stare')
                    ->options(Appointment::statuses())
                    ->required()
                    ->default('requested'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('address.city')
                    ->label('Adresă')
                    ->formatStateUsing(fn ($state, Appointment $record): string => $record->address?->line() ?? ''),
                TextColumn::make('billingProfile.company_name')
                    ->label('Facturare')
                    ->formatStateUsing(fn ($state, Appointment $record): string => $record->billingProfile?->displayName() ?? ''),
                TextColumn::make('equipment.name')
                    ->label('Echipament')
                    ->searchable(),
                TextColumn::make('requested_on')
                    ->label('Data solicitată')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stare')
                    ->formatStateUsing(fn (string $state): string => Appointment::statuses()[$state] ?? $state)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creat la')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizat la')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stare')
                    ->options(Appointment::statuses()),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageAppointments::route('/'),
        ];
    }
}
