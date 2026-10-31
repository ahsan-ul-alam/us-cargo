<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\ShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('newShipment')
                ->label('New Shipment')->icon('heroicon-o-truck')
                ->url(fn () => ShipmentResource::getUrl('create', ['customer' => $this->record->id])),
            Actions\DeleteAction::make(),
        ];
    }
}
