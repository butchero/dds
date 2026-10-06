<?php

namespace App\Filament\Resources\Products;

use App\Enums\AttributeType;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Support\ProductCharacteristics;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Produse';

    protected static ?string $modelLabel = 'produs';

    protected static ?string $pluralModelLabel = 'Produse';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_category_id')
                    ->label('Categorie')
                    ->relationship('category', 'name')
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                        $set('characteristic_values', ProductCharacteristics::rowsForCategory(
                            filled($state) ? (int) $state : null,
                            $get('characteristic_values') ?? [],
                        ));
                    }),
                TextInput::make('name')
                    ->label('Nume')
                    ->required(),
                TextInput::make('slug')
                    ->label('Slug'),
                Textarea::make('excerpt')
                    ->label('Rezumat')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descriere')
                    ->columnSpanFull(),
                FileUpload::make('gallery')
                    ->label('Poze')
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->image()
                    ->disk('public')
                    ->directory('produse')
                    ->visibility('public')
                    ->columnSpanFull(),
                TextInput::make('manufacturer')
                    ->label('Producător'),
                Section::make('Comercial')
                    ->schema([
                        TextInput::make('price')
                            ->label('Preț')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('lei')
                            ->helperText('Opțional. Catalogul poate afișa produse fără preț.')
                            ->dehydrateStateUsing(fn (mixed $state): mixed => self::emptyToNull($state)),
                        TextInput::make('quantity')
                            ->label('Cantitate')
                            ->integer()
                            ->minValue(0)
                            ->dehydrateStateUsing(fn (mixed $state): mixed => self::emptyToNull($state)),
                        TextInput::make('vat_rate')
                            ->label('Cotă TVA')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->helperText('Gol = cota globală.')
                            ->dehydrateStateUsing(fn (mixed $state): mixed => self::emptyToNull($state)),
                    ])
                    ->columnSpanFull(),
                TextEntry::make('characteristics_empty')
                    ->label('Caracteristici')
                    ->state('Categoria nu are caracteristici asignate.')
                    ->dehydrated(false)
                    ->visible(fn (Get $get): bool => filled($get('product_category_id')) && blank($get('characteristic_values')))
                    ->columnSpanFull(),
                Repeater::make('characteristic_values')
                    ->label('Caracteristici')
                    ->schema([
                        Hidden::make('attribute_id'),
                        Hidden::make('type'),
                        Hidden::make('name'),
                        Hidden::make('unit'),
                        Textarea::make('value_text')
                            ->label('Valoare')
                            ->visible(fn (Get $get): bool => $get('type') === AttributeType::Text->value)
                            ->columnSpanFull(),
                        TextInput::make('value_number')
                            ->label('Valoare')
                            ->numeric()
                            ->suffix(fn (Get $get): ?string => $get('unit'))
                            ->visible(fn (Get $get): bool => $get('type') === AttributeType::Number->value),
                        Select::make('value_boolean')
                            ->label('Valoare')
                            ->options([
                                '1' => 'Da',
                                '0' => 'Nu',
                            ])
                            ->placeholder('—')
                            ->visible(fn (Get $get): bool => $get('type') === AttributeType::Boolean->value),
                        Select::make('option_id')
                            ->label('Valoare')
                            ->options(fn (Get $get): array => self::optionChoices($get('attribute_id')))
                            ->placeholder('—')
                            ->visible(fn (Get $get): bool => $get('type') === AttributeType::Select->value),
                        Select::make('option_ids')
                            ->label('Valori')
                            ->multiple()
                            ->options(fn (Get $get): array => self::optionChoices($get('attribute_id')))
                            ->visible(fn (Get $get): bool => $get('type') === AttributeType::Multiselect->value),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->visible(fn (Get $get): bool => filled($get('characteristic_values')))
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Publicat')
                    ->required(),
                TextInput::make('sort')
                    ->label('Ordine')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nume')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                ImageColumn::make('image')
                    ->label('Poză'),
                TextColumn::make('manufacturer')
                    ->label('Producător')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('Publicat')
                    ->boolean(),
                TextColumn::make('sort')
                    ->label('Ordine')
                    ->numeric()
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
                self::configureEditAction(EditAction::make()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function configureCreateAction(CreateAction $action): CreateAction
    {
        return $action->using(function (array $data): Product {
            return ProductCharacteristics::create($data);
        });
    }

    public static function configureEditAction(EditAction $action): EditAction
    {
        return $action
            ->mutateRecordDataUsing(function (array $data, Product $record): array {
                $data['characteristic_values'] = ProductCharacteristics::rowsForProduct($record);
                $data['gallery'] = ProductCharacteristics::galleryForProduct($record);

                return $data;
            })
            ->using(function (array $data, Product $record): void {
                ProductCharacteristics::update($record, $data);
            });
    }

    /**
     * @return array<int|string, string>
     */
    public static function optionChoices(mixed $attributeId): array
    {
        if (blank($attributeId)) {
            return [];
        }

        return AttributeOption::query()
            ->where('attribute_id', $attributeId)
            ->orderBy('sort')
            ->orderBy('id')
            ->pluck('label', 'id')
            ->all();
    }

    private static function emptyToNull(mixed $state): mixed
    {
        return ($state === null || $state === '') ? null : $state;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
