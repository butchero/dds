<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class ContentBlocks
{
    public static function make(string $name = 'blocks'): Builder
    {
        return Builder::make($name)
            ->label(__('Blocuri'))
            ->blocks([
                Block::make('heading')->label(__('Titlu'))->schema([
                    TextInput::make('text')->label(__('Text'))->required(),
                ]),
                Block::make('text')->label(__('Text'))->schema([
                    Textarea::make('body')->label(__('Conținut'))->required()->rows(6),
                ]),
                Block::make('image')->label(__('Poză'))->schema([
                    FileUpload::make('path')->label(__('Poză'))->image()->disk('public')->directory('blocks')->required(),
                    TextInput::make('alt')->label(__('Text alternativ')),
                ]),
                Block::make('text_image')->label(__('Text lângă poză'))->schema([
                    Textarea::make('body')->label(__('Text'))->required()->rows(4),
                    FileUpload::make('path')->label(__('Poză'))->image()->disk('public')->directory('blocks')->required(),
                    TextInput::make('alt')->label(__('Text alternativ')),
                ]),
                Block::make('button')->label(__('Buton'))->schema([
                    TextInput::make('label')->label(__('Etichetă'))->required(),
                    TextInput::make('url')->label(__('Adresă'))->required(),
                ]),
            ])
            ->columnSpanFull();
    }
}
