<?php

namespace App\Filament\Resources\ManifestResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipments';
    protected static ?string $title = 'Shipments in this Manifest';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tracking_number')
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('sender_name')->label('Sender'),
                Tables\Columns\TextColumn::make('recipient_name')->label('Recipient'),
                Tables\Columns\TextColumn::make('weight_kg')->suffix(' kg'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'pending',
                        'primary' => 'collected',
                        'info'    => ['in_transit', 'out_for_delivery'],
                        'warning' => 'customs_clearance',
                        'success' => 'delivered',
                        'danger'  => ['returned', 'cancelled'],
                    ]),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->label('Add Shipment')
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereNull('manifest_id'))
                    ->recordTitle(fn ($record) => "{$record->tracking_number} — {$record->sender_name} → {$record->recipient_name}"),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                ]),
            ]);
    }
}
