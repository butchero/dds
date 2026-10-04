<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
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
                    ->relationship('category', 'name'),
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
                FileUpload::make('image')
                    ->label('Poză')
                    ->image()
                    ->disk('public')
                    ->directory('produse'),
                TextInput::make('manufacturer')
                    ->label('Producător'),
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
            'index' => ManageProducts::route('/'),
        ];
    }
}
