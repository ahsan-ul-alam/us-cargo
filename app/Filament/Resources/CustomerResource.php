<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Cargo Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('phone')->required(),
                Forms\Components\TextInput::make('email')->email()->nullable(),
                Forms\Components\TextInput::make('whatsapp')->nullable(),
                Forms\Components\TextInput::make('address_uk')->label('UK Address')->nullable(),
                Forms\Components\TextInput::make('address_bd')->label('BD Address')->nullable(),
                Forms\Components\Select::make('country')
                    ->options(['uk' => 'UK', 'bd' => 'Bangladesh', 'both' => 'Both'])
                    ->default('both'),
                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('shipments_count')
                    ->counts('shipments')->label('Shipments')->sortable(),
                Tables\Columns\BadgeColumn::make('country')
                    ->colors(['info' => 'uk', 'success' => 'bd', 'primary' => 'both']),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->options(['uk' => 'UK', 'bd' => 'Bangladesh', 'both' => 'Both']),
            ])
            ->actions([
                Tables\Actions\Action::make('newShipment')
                    ->label('New Shipment')->icon('heroicon-o-truck')->color('primary')
                    ->url(fn (Customer $record) => ShipmentResource::getUrl('create', ['customer' => $record->id])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
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
