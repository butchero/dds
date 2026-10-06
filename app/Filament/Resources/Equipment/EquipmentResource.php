<?php

namespace App\Filament\Resources\Equipment;

use App\Filament\Resources\Equipment\Pages\ManageEquipment;
use App\Models\Address;
use App\Models\Equipment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Echipamente';

    protected static ?string $modelLabel = 'echipament';

    protected static ?string $pluralModelLabel = 'Echipamente';

    protected static string|\UnitEnum|null $navigationGroup = 'Clienți';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Client')
                    ->relationship('user', 'name')
                    ->required()
                    ->live(),
                TextInput::make('name')
                    ->label('Nume')
                    ->required(),
                TextInput::make('type')
                    ->label('Tip'),
                Select::make('address_id')
                    ->label('Adresă')
                    ->options(fn (Get $get): array => Address::query()
                        ->where('user_id', $get('user_id'))
                        ->get()
                        ->mapWithKeys(fn (Address $address): array => [$address->id => $address->line()])
                        ->all())
                    ->searchable(),
                Select::make('interval_months')
                    ->label('Interval (luni)')
                    ->options([12 => '12', 24 => '24'])
                    ->default(24)
                    ->required(),
                DatePicker::make('last_revision_on')
                    ->label('Ultima revizie'),
                Toggle::make('notify_email')
                    ->label('Notificare pe email'),
                Toggle::make('notify_sms')
                    ->label('Notificare pe SMS'),
                DatePicker::make('next_revision_on')
                    ->label('Următoarea revizie'),
                DatePicker::make('notified_on')
                    ->label('Notificat la'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nume')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tip')
                    ->searchable(),
                TextColumn::make('last_revision_on')
                    ->label('Ultima revizie')
                    ->date()
                    ->sortable(),
                TextColumn::make('interval_months')
                    ->label('Interval (luni)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('next_revision_on')
                    ->label('Următoarea revizie')
                    ->date()
                    ->sortable(),
                TextColumn::make('notified_on')
                    ->label('Notificat la')
                    ->date()
                    ->sortable(),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageEquipment::route('/'),
        ];
    }
}
