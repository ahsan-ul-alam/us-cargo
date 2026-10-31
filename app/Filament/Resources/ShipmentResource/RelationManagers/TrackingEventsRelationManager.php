<?php

namespace App\Filament\Resources\ShipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TrackingEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'trackingEvents';
    protected static ?string $title = 'Tracking Timeline';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DateTimePicker::make('occurred_at')
                ->label('Date & Time')->required()->default(now()),
            Forms\Components\TextInput::make('status')
                ->label('Status')->required()
                ->placeholder('e.g. Collected, In Transit, Delivered'),
            Forms\Components\TextInput::make('location')
                ->label('Location')
                ->placeholder('e.g. London Warehouse, Dhaka Customs'),
            Forms\Components\Textarea::make('description')
                ->label('Description')->required()->columnSpanFull()
                ->placeholder('e.g. Package collected from sender address'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('occurred_at')->dateTime()->label('Time')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\TextColumn::make('description')->wrap(),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->headerActions([Tables\Actions\CreateAction::make()->label('Add Event')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }
}
