<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentResource\Pages;
use App\Models\Shipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Shipments';
    protected static ?string $navigationGroup = 'Cargo Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Tracking & Customer')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('tracking_number')
                        ->label('Tracking Number')
                        ->default(fn () => Shipment::generateTrackingNumber())
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->readOnly(),
                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\Select::make('manifest_id')
                        ->label('Manifest')
                        ->relationship('manifest', 'manifest_number')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending'           => 'Pending',
                            'collected'         => 'Collected',
                            'in_transit'        => 'In Transit',
                            'customs_clearance' => 'Customs Clearance',
                            'out_for_delivery'  => 'Out for Delivery',
                            'delivered'         => 'Delivered',
                            'returned'          => 'Returned',
                            'cancelled'         => 'Cancelled',
                        ])
                        ->required(),
                    Forms\Components\Select::make('service_type')
                        ->options([
                            'air_cargo'    => 'Air Cargo',
                            'door_to_door' => 'Door to Door',
                            'parcel'       => 'Parcel',
                        ])
                        ->required(),
                ]),

            Forms\Components\Section::make('Sender Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('sender_name')->required(),
                    Forms\Components\TextInput::make('sender_phone')->required(),
                    Forms\Components\TextInput::make('sender_address')->required()->columnSpanFull(),
                    Forms\Components\Select::make('sender_country')
                        ->options(['uk' => 'United Kingdom', 'bd' => 'Bangladesh'])
                        ->default('uk')->required(),
                ]),

            Forms\Components\Section::make('Recipient Details')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('recipient_name')->required(),
                    Forms\Components\TextInput::make('recipient_phone')->required(),
                    Forms\Components\TextInput::make('recipient_address')->required()->columnSpanFull(),
                    Forms\Components\Select::make('recipient_country')
                        ->options(['uk' => 'United Kingdom', 'bd' => 'Bangladesh'])
                        ->default('bd')->required(),
                ]),

            Forms\Components\Section::make('Shipment Details')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('weight_kg')
                        ->label('Weight (kg)')->numeric()->required(),
                    Forms\Components\TextInput::make('price_gbp')
                        ->label('Price (£)')->numeric()->nullable(),
                    Forms\Components\DatePicker::make('estimated_delivery')
                        ->label('Estimated Delivery'),
                    Forms\Components\DatePicker::make('actual_delivery')
                        ->label('Actual Delivery'),
                    Forms\Components\Textarea::make('description')
                        ->label('Contents Description')->columnSpanFull(),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Admin Notes')->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Payment')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')
                        ->default(fn () => Shipment::generateInvoiceNumber())
                        ->unique(ignoreRecord: true)
                        ->readOnly(),
                    Forms\Components\Select::make('payment_status')
                        ->options(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'])
                        ->default('unpaid')->required()->live(),
                    Forms\Components\Select::make('payment_method')
                        ->options(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card', 'other' => 'Other'])
                        ->visible(fn (Forms\Get $get) => $get('payment_status') !== 'unpaid'),
                    Forms\Components\TextInput::make('paid_amount')
                        ->label('Paid Amount (£)')->numeric()->default(0)
                        ->visible(fn (Forms\Get $get) => $get('payment_status') !== 'unpaid'),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->label('Paid At')
                        ->visible(fn (Forms\Get $get) => $get('payment_status') !== 'unpaid'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('sender_name')
                    ->searchable()->label('Sender'),
                Tables\Columns\TextColumn::make('recipient_name')
                    ->searchable()->label('Recipient'),
                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Weight')->suffix(' kg')->sortable(),
                Tables\Columns\TextColumn::make('price_gbp')
                    ->label('Price')->prefix('£')->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors(['danger' => 'unpaid', 'warning' => 'partial', 'success' => 'paid']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'pending',
                        'primary' => 'collected',
                        'info'    => ['in_transit', 'out_for_delivery'],
                        'warning' => 'customs_clearance',
                        'success' => 'delivered',
                        'danger'  => ['returned', 'cancelled'],
                    ]),
                Tables\Columns\TextColumn::make('manifest.manifest_number')
                    ->label('Manifest')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estimated_delivery')
                    ->label('Est. Delivery')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'           => 'Pending',
                        'collected'         => 'Collected',
                        'in_transit'        => 'In Transit',
                        'customs_clearance' => 'Customs Clearance',
                        'out_for_delivery'  => 'Out for Delivery',
                        'delivered'         => 'Delivered',
                        'returned'          => 'Returned',
                        'cancelled'         => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('service_type')
                    ->options([
                        'air_cargo'    => 'Air Cargo',
                        'door_to_door' => 'Door to Door',
                        'parcel'       => 'Parcel',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid']),
            ])
            ->actions([
                Tables\Actions\Action::make('addTracking')
                    ->label('Add Tracking')
                    ->icon('heroicon-o-map-pin')
                    ->color('success')
                    ->modalHeading(fn (Shipment $record) => 'Add tracking event — ' . $record->tracking_number)
                    ->modalSubmitActionLabel('Add event')
                    ->form(ShipmentResource\TrackingEventModal::form())
                    ->action(fn (Shipment $record, array $data) => ShipmentResource\TrackingEventModal::handle($record, $data)),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('assignManifest')
                        ->label('Assign to Manifest')
                        ->icon('heroicon-o-archive-box')
                        ->form([
                            Forms\Components\Select::make('manifest_id')
                                ->label('Manifest')
                                ->options(fn () => \App\Models\Manifest::query()->pluck('manifest_number', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records, array $data) => $records->each->update(['manifest_id' => $data['manifest_id']]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Shipment Overview')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('tracking_number')->copyable()->weight('bold'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (Shipment $record) => $record->status_color),
                    Infolists\Components\TextEntry::make('service_type')->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('weight_kg')->suffix(' kg'),
                    Infolists\Components\TextEntry::make('price_gbp')->prefix('£'),
                    Infolists\Components\TextEntry::make('estimated_delivery')->date(),
                ]),

            Infolists\Components\Section::make('Payment')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('invoice_number')->copyable(),
                    Infolists\Components\TextEntry::make('payment_status')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'paid'    => 'success',
                            'partial' => 'warning',
                            default   => 'danger',
                        }),
                    Infolists\Components\TextEntry::make('paid_amount')->prefix('£'),
                    Infolists\Components\TextEntry::make('balance_due')->prefix('£'),
                ]),

            Infolists\Components\Section::make('Tracking Timeline')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('trackingEvents')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('occurred_at')->dateTime()->label('Time'),
                            Infolists\Components\TextEntry::make('status')->badge()->label('Status'),
                            Infolists\Components\TextEntry::make('location')->label('Location'),
                            Infolists\Components\TextEntry::make('description')->label('Description'),
                        ])
                        ->columns(4),
                ]),

            Infolists\Components\Section::make('Sender & Recipient')
                ->columns(2)
                ->schema([
                    Infolists\Components\Group::make([
                        Infolists\Components\TextEntry::make('sender_name')->label('Name'),
                        Infolists\Components\TextEntry::make('sender_phone')->label('Phone'),
                        Infolists\Components\TextEntry::make('sender_address')->label('Address'),
                        Infolists\Components\TextEntry::make('sender_country')->label('Country')->badge(),
                    ])->label('Sender'),
                    Infolists\Components\Group::make([
                        Infolists\Components\TextEntry::make('recipient_name')->label('Name'),
                        Infolists\Components\TextEntry::make('recipient_phone')->label('Phone'),
                        Infolists\Components\TextEntry::make('recipient_address')->label('Address'),
                        Infolists\Components\TextEntry::make('recipient_country')->label('Country')->badge(),
                    ])->label('Recipient'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            ShipmentResource\RelationManagers\TrackingEventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListShipments::route('/'),
            'create' => Pages\CreateShipment::route('/create'),
            'view'   => Pages\ViewShipment::route('/{record}'),
            'edit'   => Pages\EditShipment::route('/{record}/edit'),
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
