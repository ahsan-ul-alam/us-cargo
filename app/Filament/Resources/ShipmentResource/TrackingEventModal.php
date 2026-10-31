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
    public const STATUSES = [
        'pending'           => 'Pending',
        'collected'         => 'Collected',
        'in_transit'        => 'In Transit',
        'customs_clearance' => 'Customs Clearance',
        'out_for_delivery'  => 'Out for Delivery',
        'delivered'         => 'Delivered',
        'returned'          => 'Returned',
        'cancelled'         => 'Cancelled',
    ];

    public static function form(): array
    {
        return [
            Forms\Components\Select::make('shipment_status')
                ->label('Shipment status')
                ->options(self::STATUSES)
                ->default(fn (Shipment $record) => $record->status)
                ->live()
                ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('status', self::STATUSES[$state] ?? null))
                ->required(),
            Forms\Components\TextInput::make('status')
                ->label('Event title (shown to customer)')
                ->default(fn (Shipment $record) => self::STATUSES[$record->status] ?? null)
                ->required(),
            Forms\Components\TextInput::make('location')
                ->placeholder('e.g. London Warehouse, Dhaka Customs'),
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
