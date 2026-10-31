<?php

namespace App\Filament\Resources\ManifestResource\Pages;

use App\Filament\Resources\ManifestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManifest extends EditRecord
{
    protected static string $resource = ManifestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
