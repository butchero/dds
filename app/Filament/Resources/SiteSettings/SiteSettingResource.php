<?php

namespace App\Filament\Resources\SiteSettings;

use App\Filament\Resources\SiteSettings\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Meniul principal';

    protected static ?string $modelLabel = 'setare';

    protected static ?string $pluralModelLabel = 'Meniu';

    protected static string|\UnitEnum|null $navigationGroup = 'Conținut';

    protected static ?string $recordTitleAttribute = 'value';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('value')
                    ->label('Poziție pe desktop')
                    ->options(self::menuPositions())
                    ->helperText(fn (?SiteSetting $record): string => $record?->description
                        ?: SiteSetting::definitions()['menu_position']['description'])
                    ->required()
                    ->default('top'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                TextColumn::make('value')
                    ->label('Poziție pe desktop')
                    ->formatStateUsing(fn (string $state): string => self::menuPositions()[$state] ?? $state)
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
            ]);
    }

    /**
     * @return Builder<SiteSetting>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('name', 'menu_position');
    }

    /**
     * @return array<string, string>
     */
    public static function menuPositions(): array
    {
        return [
            'top' => 'Sus, lângă siglă',
            'left' => 'Stânga, deasupra categoriilor',
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
