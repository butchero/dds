<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('Bannere');
    }

    public static function getModelLabel(): string
    {
        return __('banner');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bannere');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('Conținut');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('Titlu'))
                    ->required(),
                FileUpload::make('image')
                    ->label(__('Imagine'))
                    ->image()
                    ->disk('public')
                    ->directory('banners')
                    ->required(),
                TextInput::make('link')
                    ->label(__('Adresă')),
                TextInput::make('sort')
                    ->label(__('Ordine'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_published')
                    ->label(__('Publicat'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Titlu'))
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(__('Imagine'))
                    ->disk('public'),
                TextColumn::make('link')
                    ->label(__('Adresă'))
                    ->searchable(),
                TextColumn::make('sort')
                    ->label(__('Ordine'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label(__('Publicat'))
                    ->boolean(),
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

    public static function getPages(): array
    {
        return [
            'index' => ManageBanners::route('/'),
        ];
    }
}
