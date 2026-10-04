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
            ->label('Blocuri')
            ->blocks([
                Block::make('heading')->label('Titlu')->schema([
                    TextInput::make('text')->label('Text')->required(),
                ]),
                Block::make('text')->label('Text')->schema([
                    Textarea::make('body')->label('Conținut')->required()->rows(6),
                ]),
                Block::make('image')->label('Poză')->schema([
                    FileUpload::make('path')->label('Poză')->image()->disk('public')->directory('blocks')->required(),
                    TextInput::make('alt')->label('Text alternativ'),
                ]),
                Block::make('text_image')->label('Text lângă poză')->schema([
                    Textarea::make('body')->label('Text')->required()->rows(4),
                    FileUpload::make('path')->label('Poză')->image()->disk('public')->directory('blocks')->required(),
                    TextInput::make('alt')->label('Text alternativ'),
                ]),
                Block::make('button')->label('Buton')->schema([
                    TextInput::make('label')->label('Etichetă')->required(),
                    TextInput::make('url')->label('Adresă')->required(),
                ]),
            ])
            ->columnSpanFull();
    }
}
