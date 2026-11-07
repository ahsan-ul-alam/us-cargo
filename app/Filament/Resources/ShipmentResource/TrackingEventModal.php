<?php

namespace App\Filament\Resources\ShipmentResource;

use App\Models\Shipment;
use Filament\Forms;
use Filament\Notifications\Notification;

/**
 * Quick "Add Tracking Event" modal, shared by the shipments list row action
 * and the shipment view-page header action (Filament modals submit via Livewire/ajax).
 */
class TrackingEventModal
{
    public static function form(): array
    {
        return [
            Forms\Components\Toggle::make('is_sea')
                ->label('Sea freight (default: Air)')
                ->default(fn (Shipment $record) => $record->transport_mode === 'sea')
                ->live()
                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set, Shipment $record) {
                    // Keep the chosen status valid for the selected mode.
                    $mode = $state ? 'sea' : 'air';
                    if (! array_key_exists($get('shipment_status'), Shipment::statusOptions($mode))) {
                        $set('shipment_status', 'collected');
                        $set('status', Shipment::allStatusOptions()['collected']);
                    }
                }),
            // Options follow the selected transport mode (air or sea).
            Forms\Components\Select::make('shipment_status')
                ->label('Shipment status')
                ->options(fn (Forms\Get $get) => Shipment::statusOptions($get('is_sea') ? 'sea' : 'air'))
                ->default(fn (Shipment $record) => $record->status)
                ->live()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('status', Shipment::allStatusOptions()[$state] ?? null))
                ->required(),
            Forms\Components\TextInput::make('status')
                ->label('Event title (shown to customer)')
                ->default(fn (Shipment $record) => Shipment::allStatusOptions()[$record->status] ?? null)
                ->required(),
            Forms\Components\TextInput::make('location')
                ->placeholder('e.g. Heathrow Airport, Chattogram Port, Dhaka Office'),
            Forms\Components\Textarea::make('description')
                ->required()
                ->placeholder('e.g. Package arrived at Dhaka airport'),
            Forms\Components\DateTimePicker::make('occurred_at')
                ->label('Date & Time')->default(now())->required(),
        ];
    }

    public static function handle(Shipment $record, array $data): void
    {
        $record->trackingEvents()->create([
            'status'      => $data['status'],
            'location'    => $data['location'] ?? null,
            'description' => $data['description'],
            'occurred_at' => $data['occurred_at'],
        ]);

        $updates = [];
        $mode = ! empty($data['is_sea']) ? 'sea' : 'air';
        if ($mode !== $record->transport_mode) {
            $updates['transport_mode'] = $mode;
        }
        if ($data['shipment_status'] !== $record->status) {
            $updates['status'] = $data['shipment_status'];
        }
        if ($data['shipment_status'] === 'delivered' && ! $record->actual_delivery) {
            $updates['actual_delivery'] = now();
        }
        if ($updates) {
            $record->update($updates);
        }

        Notification::make()->title('Tracking event added')->success()->send();
    }
}
