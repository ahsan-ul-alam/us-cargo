<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingRateResource\Pages;
use App\Models\ShippingRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingRateResource extends Resource
{
    protected static ?string $model = ShippingRate::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-pound';
    protected static ?string $navigationLabel = 'Shipping Rates';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\Select::make('direction')
                    ->options(['uk_to_bd' => 'UK → Bangladesh', 'bd_to_uk' => 'Bangladesh → UK'])
                    ->required(),
                Forms\Components\Select::make('service_type')
                    ->options(['air_cargo' => 'Air Cargo', 'door_to_door' => 'Door to Door', 'parcel' => 'Parcel'])
                    ->required(),
                Forms\Components\TextInput::make('price_per_kg')
                    ->label('Price per kg')->numeric()->required()->prefix('£'),
                Forms\Components\TextInput::make('min_weight_kg')
                    ->label('Minimum Weight (kg)')->numeric()->default(1)->required(),
                Forms\Components\TextInput::make('currency')->default('GBP')->required(),
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('direction')
                    ->formatStateUsing(fn ($state) => $state === 'uk_to_bd' ? 'UK → BD' : 'BD → UK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title())
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('price_per_kg')->label('Rate')->prefix('£')->suffix('/kg')->sortable(),
                Tables\Columns\TextColumn::make('min_weight_kg')->label('Min Weight')->suffix(' kg'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->options(['uk_to_bd' => 'UK → Bangladesh', 'bd_to_uk' => 'Bangladesh → UK']),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('direction');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageShippingRates::route('/'),
        ];
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
