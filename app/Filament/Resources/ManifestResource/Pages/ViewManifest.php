<?php

namespace App\Filament\Resources\ManifestResource\Pages;

use App\Filament\Resources\ManifestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewManifest extends ViewRecord
{
    protected static string $resource = ManifestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
