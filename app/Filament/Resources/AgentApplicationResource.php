<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentApplicationResource\Pages;
use App\Models\AgentApplication;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgentApplicationResource extends Resource
{
    protected static ?string $model = AgentApplication::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Agent Applications';
    protected static ?string $navigationGroup = 'Enquiries';
    protected static ?int $navigationSort = 5;

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Applicant')->columns(2)->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('company_name')->label('Company'),
                Infolists\Components\TextEntry::make('phone')->copyable(),
                Infolists\Components\TextEntry::make('email')->copyable(),
                Infolists\Components\TextEntry::make('address')->columnSpanFull(),
                Infolists\Components\TextEntry::make('created_at')->label('Applied at')->dateTime(),
            ]),
            Infolists\Components\Section::make('Decision')->columns(2)->schema([
                Infolists\Components\TextEntry::make('status')->badge()
                    ->formatStateUsing(fn ($state) => AgentApplication::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => static::statusColor($state)),
                Infolists\Components\TextEntry::make('reviewer.name')->label('Reviewed by')->placeholder('—'),
                Infolists\Components\TextEntry::make('reviewed_at')->dateTime()->placeholder('—'),
                Infolists\Components\TextEntry::make('admin_note')->label('Note')->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('company_name')->label('Company')->searchable(),
            Tables\Columns\TextColumn::make('phone')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable()->toggleable(),
            Tables\Columns\TextColumn::make('address')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('status')->badge()
                ->formatStateUsing(fn ($state) => AgentApplication::STATUSES[$state] ?? $state)
                ->color(fn ($state) => static::statusColor($state)),
            Tables\Columns\TextColumn::make('created_at')->label('Applied')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('status')->options(AgentApplication::STATUSES),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            static::approveAction(),
            static::rejectAction(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])->defaultSort('created_at', 'desc');
    }

    protected static function approveAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('approve')
            ->label('Approve')->icon('heroicon-o-check-circle')->color('success')
            ->visible(fn (AgentApplication $record) => $record->status !== AgentApplication::STATUS_APPROVED)
            ->requiresConfirmation()
            ->modalHeading('Approve as agent?')
            ->form([
                Forms\Components\Textarea::make('admin_note')->label('Note (optional)')->rows(3),
            ])
            ->action(function (AgentApplication $record, array $data) {
                $record->decide(AgentApplication::STATUS_APPROVED, $data['admin_note'] ?? null);
                Notification::make()->title("{$record->name} approved as agent")->success()->send();
            });
    }

    protected static function rejectAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('reject')
            ->label('Reject')->icon('heroicon-o-x-circle')->color('danger')
            ->visible(fn (AgentApplication $record) => $record->status !== AgentApplication::STATUS_REJECTED)
            ->requiresConfirmation()
            ->modalHeading('Reject this application?')
            ->form([
                Forms\Components\Textarea::make('admin_note')->label('Reason (optional)')->rows(3),
            ])
            ->action(function (AgentApplication $record, array $data) {
                $record->decide(AgentApplication::STATUS_REJECTED, $data['admin_note'] ?? null);
                Notification::make()->title("{$record->name}'s application rejected")->danger()->send();
            });
    }

    protected static function statusColor(?string $state): string
    {
        return match ($state) {
            AgentApplication::STATUS_APPROVED => 'success',
            AgentApplication::STATUS_REJECTED => 'danger',
            default                           => 'warning',
        };
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAgentApplications::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        return AgentApplication::where('status', AgentApplication::STATUS_PENDING)->count() ?: null;
    }

    public static function canCreate(): bool
    {
        return false; // applications only come from the public form
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
