<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use Filament\Resources\Pages\ManageRecords;

class ManageSiteSettings extends ManageRecords
{
    protected static string $resource = SiteSettingResource::class;

    public function mount(): void
    {
        SiteSetting::ensureDefaults();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
