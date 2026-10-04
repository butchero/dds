<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\ManageAppointments;
use App\Models\Appointment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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
                    ->required(),
                Select::make('equipment_id')
                    ->label('Echipament')
                    ->relationship('equipment', 'name'),
                DatePicker::make('requested_on')
                    ->label('Data solicitată'),
                Textarea::make('note')
                    ->label('Notă')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Stare')
                    ->options(self::statuses())
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
                TextColumn::make('equipment.name')
                    ->label('Echipament')
                    ->searchable(),
                TextColumn::make('requested_on')
                    ->label('Data solicitată')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stare')
                    ->formatStateUsing(fn (string $state): string => self::statuses()[$state] ?? $state)
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
    public static function statuses(): array
    {
        return [
            'requested' => 'Cerută',
            'confirmed' => 'Confirmată',
            'done' => 'Efectuată',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAppointments::route('/'),
        ];
    }
}
