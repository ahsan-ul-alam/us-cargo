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
                Tables\Columns\TextColumn::make('sender_name')->label('Sender')->state(fn ($record) => $record->sender_label),
                Tables\Columns\TextColumn::make('recipient_name')->label('Recipient')->state(fn ($record) => $record->recipient_label),
                Tables\Columns\TextColumn::make('weight_kg')->suffix(' kg'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => \App\Models\Shipment::allStatusOptions()[$state] ?? $state)
                    ->color(fn ($state) => (new \App\Models\Shipment(['status' => $state]))->status_color),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make()
                    ->label('Add Shipment')
                    ->recordSelectOptionsQuery(fn ($query) => $query->whereNull('manifest_id'))
                    ->recordTitle(fn ($record) => "{$record->tracking_number} — {$record->sender_label} → {$record->recipient_label}"),
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
