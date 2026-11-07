<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ShipmentResource;
use App\Models\Shipment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestShipments extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Latest shipments';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Shipment::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')->weight('bold')->copyable(),
                Tables\Columns\TextColumn::make('sender_name')->label('Sender')->state(fn ($record) => $record->sender_label),
                Tables\Columns\TextColumn::make('recipient_name')->label('Recipient')->state(fn ($record) => $record->recipient_label),
                Tables\Columns\TextColumn::make('weight_kg')->label('Weight')->suffix(' kg'),
                Tables\Columns\TextColumn::make('price_gbp')->label('Price')->prefix('£'),
                Tables\Columns\TextColumn::make('payment_status')->label('Payment')->badge()
                    ->color(fn ($state) => match ($state) { 'paid' => 'success', 'partial' => 'warning', default => 'danger' }),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (Shipment $record) => $record->status_color),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Created'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Open')->icon('heroicon-o-eye')
                    ->url(fn (Shipment $record) => ShipmentResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('No shipments yet');
    }
}
