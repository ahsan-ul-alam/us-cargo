<?php

namespace App\Filament\Resources\PopupResource\Pages;

use App\Filament\Resources\PopupResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePopups extends ManageRecords
{
    protected static string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
