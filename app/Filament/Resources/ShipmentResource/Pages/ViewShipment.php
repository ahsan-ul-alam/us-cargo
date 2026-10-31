<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Filament\Resources\ShipmentResource\TrackingEventModal;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewShipment extends ViewRecord
{
    protected static string $resource = ShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addTracking')
                ->label('Add Tracking')
                ->icon('heroicon-o-map-pin')
                ->color('success')
                ->modalSubmitActionLabel('Add event')
                ->form(TrackingEventModal::form())
                ->action(fn (array $data) => TrackingEventModal::handle($this->record, $data)),
            Actions\Action::make('invoice')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('admin.invoice', $this->record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
