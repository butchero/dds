<?php

namespace App\Filament\Resources\News;

use App\Filament\Resources\News\Pages\ManageNews;
use App\Filament\Support\ContentBlocks;
use App\Models\News;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('Știri');
    }

    public static function getModelLabel(): string
    {
        return __('știre');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Știri');
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
                TextInput::make('slug')
                    ->label('Slug'),
                Textarea::make('excerpt')
                    ->label(__('Rezumat'))
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label(__('Poză'))
                    ->image()
                    ->disk('public')
                    ->directory('stiri'),
                ContentBlocks::make(),
                DateTimePicker::make('published_at')
                    ->label(__('Publicat la')),
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
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                ImageColumn::make('image')
                    ->label(__('Poză')),
                TextColumn::make('published_at')
                    ->label(__('Publicat la'))
                    ->dateTime()
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
            'index' => ManageNews::route('/'),
        ];
    }
}
