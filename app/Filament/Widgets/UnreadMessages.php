<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UnreadMessages extends BaseWidget
{
    protected static ?int $sort = 6;

    protected static ?string $heading = 'Unread contact messages';

    public function table(Table $table): Table
    {
        return $table
            ->query(ContactMessage::query()->where('status', 'unread')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')->weight('bold'),
                Tables\Columns\TextColumn::make('subject')->limit(28)->placeholder('—'),
                Tables\Columns\TextColumn::make('message')->limit(40),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Received'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Open')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ContactMessage $record) => ContactMessageResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No unread messages')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
