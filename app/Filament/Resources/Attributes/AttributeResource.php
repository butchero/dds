<?php

namespace App\Filament\Resources\Attributes;

use App\Enums\AttributeType;
use App\Filament\Resources\Attributes\Pages\ManageAttributes;
use App\Models\Attribute;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Caracteristici');
    }

    public static function getModelLabel(): string
    {
        return __('caracteristică');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Caracteristici');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Catalog');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Nume'))
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug'),
                Select::make('type')
                    ->label(__('Tip'))
                    ->options(self::typeOptions())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                        $type = AttributeType::tryFrom((string) $state);
                        $set('is_filterable', $type?->filtersByDefault() ?? false);
                    }),
                TextInput::make('unit')
                    ->label(__('Unitate'))
                    ->maxLength(20)
                    ->visible(fn (Get $get): bool => $get('type') === AttributeType::Number->value),
                Toggle::make('is_filterable')
                    ->label(__('Se folosește la filtrare')),
                TextInput::make('sort')
                    ->label(__('Ordine'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Repeater::make('options')
                    ->label(__('Valori'))
                    ->relationship()
                    ->schema([
                        TextInput::make('label')
                            ->label(__('Valoare'))
                            ->required()
                            ->distinct(),
                        TextInput::make('slug')
                            ->label('Slug'),
                    ])
                    ->orderColumn('sort')
                    ->visible(fn (Get $get): bool => in_array($get('type'), [
                        AttributeType::Select->value,
                        AttributeType::Multiselect->value,
                    ], true))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nume'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('Tip'))
                    ->formatStateUsing(fn (AttributeType $state): string => $state->label()),
                TextColumn::make('unit')
                    ->label(__('Unitate')),
                IconColumn::make('is_filterable')
                    ->label(__('Filtrare'))
                    ->boolean(),
                TextColumn::make('sort')
                    ->label(__('Ordine'))
                    ->numeric()
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
    public static function typeOptions(): array
    {
        $options = [];

        foreach (AttributeType::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttributes::route('/'),
        ];
    }
}
