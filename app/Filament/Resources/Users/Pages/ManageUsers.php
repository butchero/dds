<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Imports\ClientImporter;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(ClientImporter::class)
                ->label('Importă clienți'),
            CreateAction::make(),
        ];
    }
}
