<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManifestResource\Pages;
use App\Models\Manifest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ManifestResource extends Resource
{
    protected static ?string $model = Manifest::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Manifests';
    protected static ?string $navigationGroup = 'Cargo Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('manifest_number')
                    ->default(fn () => Manifest::generateManifestNumber())
                    ->required()->unique(ignoreRecord: true)->readOnly(),
                Forms\Components\Select::make('direction')
                    ->options(['uk_to_bd' => 'UK → Bangladesh', 'bd_to_uk' => 'Bangladesh → UK'])
                    ->default('uk_to_bd')->required(),
                Forms\Components\Select::make('transport_type')
                    ->options(['air' => 'Air', 'sea' => 'Sea'])
                    ->default('air')->required()->live(),
                Forms\Components\TextInput::make('carrier_name')->label('Carrier/Airline'),
                Forms\Components\TextInput::make('flight_or_vessel_number')
                    ->label(fn (Forms\Get $get) => $get('transport_type') === 'sea' ? 'Vessel Number' : 'Flight Number'),
                Forms\Components\Select::make('status')
                    ->options([
                        'preparing' => 'Preparing',
                        'departed'  => 'Departed',
                        'arrived'   => 'Arrived',
                        'cleared'   => 'Cleared',
                    ])->default('preparing')->required(),
                Forms\Components\DatePicker::make('departure_date'),
                Forms\Components\DatePicker::make('arrival_date'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('manifest_number')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('direction')
                    ->formatStateUsing(fn ($state) => $state === 'uk_to_bd' ? 'UK → BD' : 'BD → UK'),
                Tables\Columns\TextColumn::make('transport_type')->badge()
                    ->colors(['info' => 'air', 'primary' => 'sea']),
                Tables\Columns\TextColumn::make('flight_or_vessel_number')->label('Flight/Vessel #'),
                Tables\Columns\TextColumn::make('shipments_count')->counts('shipments')->label('Shipments'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'preparing',
                        'info'    => 'departed',
                        'warning' => 'arrived',
                        'success' => 'cleared',
                    ]),
                Tables\Columns\TextColumn::make('departure_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('arrival_date')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'preparing' => 'Preparing',
                        'departed'  => 'Departed',
                        'arrived'   => 'Arrived',
                        'cleared'   => 'Cleared',
                    ]),
                Tables\Filters\SelectFilter::make('transport_type')
                    ->options(['air' => 'Air', 'sea' => 'Sea']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ManifestResource\RelationManagers\ShipmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListManifests::route('/'),
            'create' => Pages\CreateManifest::route('/create'),
            'view'   => Pages\ViewManifest::route('/{record}'),
            'edit'   => Pages\EditManifest::route('/{record}/edit'),
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
}
