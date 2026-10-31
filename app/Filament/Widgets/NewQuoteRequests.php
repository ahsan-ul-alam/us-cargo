<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\QuoteRequestResource;
use App\Models\QuoteRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class NewQuoteRequests extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'New quote requests';

    public function table(Table $table): Table
    {
        return $table
            ->query(QuoteRequest::query()->where('status', 'new')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('weight_kg')->label('Weight')->suffix(' kg')->placeholder('—'),
                Tables\Columns\TextColumn::make('direction')
                    ->formatStateUsing(fn ($state) => $state === 'uk_to_bd' ? 'UK → BD' : 'BD → UK'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Received'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Open')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn () => QuoteRequestResource::getUrl('index')),
            ])
            ->emptyStateHeading('No new quote requests')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
