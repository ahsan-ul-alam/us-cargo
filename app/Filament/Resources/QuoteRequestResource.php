<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Models\QuoteRequest;
use App\Services\ConfirmationRequired;
use App\Services\QuoteConversionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Quote Requests';
    protected static ?string $navigationGroup = 'Enquiries';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('phone')->required(),
                Forms\Components\TextInput::make('email')->email()->nullable(),
                Forms\Components\TextInput::make('weight_kg')->label('Weight (kg)')->numeric()->nullable(),
                Forms\Components\Select::make('direction')
                    ->options(['uk_to_bd' => 'UK → Bangladesh', 'bd_to_uk' => 'Bangladesh → UK'])
                    ->default('uk_to_bd')->required(),
                Forms\Components\Select::make('service_type')
                    ->options(['air_cargo' => 'Air Cargo', 'door_to_door' => 'Door to Door', 'parcel' => 'Parcel'])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'closed' => 'Closed'])
                    ->default('new'),
                Forms\Components\Textarea::make('description')->label('Description')->columnSpanFull(),
                Forms\Components\Textarea::make('admin_notes')->label('Admin Notes')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->placeholder('—'),
                Tables\Columns\TextColumn::make('shipment.tracking_number')->label('Shipment')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('weight_kg')->suffix(' kg')->label('Weight'),
                Tables\Columns\TextColumn::make('direction')
                    ->formatStateUsing(fn ($state) => $state === 'uk_to_bd' ? 'UK → BD' : 'BD → UK'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['gray' => 'new', 'warning' => 'contacted', 'success' => 'converted', 'danger' => 'closed']),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['new' => 'New', 'contacted' => 'Contacted', 'converted' => 'Converted', 'closed' => 'Closed']),
            ])
            ->actions([
                static::convertAction(),
                static::createShipmentAction(),
                Tables\Actions\Action::make('viewCustomer')
                    ->label('View Customer')->icon('heroicon-o-user')->color('gray')
                    ->visible(fn (QuoteRequest $record) => (bool) $record->customer_id)
                    ->url(fn (QuoteRequest $record) => CustomerResource::getUrl('edit', ['record' => $record->customer_id])),
                Tables\Actions\Action::make('viewShipment')
                    ->label('View Shipment')->icon('heroicon-o-truck')->color('gray')
                    ->visible(fn (QuoteRequest $record) => (bool) $record->shipment_id)
                    ->url(fn (QuoteRequest $record) => ShipmentResource::getUrl('view', ['record' => $record->shipment_id])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markContacted')
                        ->label('Mark as Contacted')
                        ->icon('heroicon-o-phone')
                        ->visible(fn () => static::canConvert())
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            abort_unless(static::canConvert(), 403);
                            // only "new" quotes move forward; converted/closed ones are never reverted
                            $count = QuoteRequest::whereIn('id', $records->pluck('id'))
                                ->where('status', 'new')->update(['status' => 'contacted']);
                            Notification::make()->success()
                                ->title("{$count} quote(s) marked as contacted")->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canConvert(): bool
    {
        return auth()->user()?->canConvertQuotes() ?? false;
    }

    protected static function convertAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('convertToCustomer')
            ->label('Convert to Customer')
            ->icon('heroicon-o-user-plus')->color('success')
            ->visible(fn (QuoteRequest $record) => static::canConvert() && ! $record->customer_id && $record->status !== 'closed')
            ->requiresConfirmation()
            ->modalHeading('Convert to customer')
            ->modalDescription(fn (QuoteRequest $record) => static::resolutionDescription($record))
            ->form(fn (QuoteRequest $record) => static::resolutionForm($record))
            ->action(function (QuoteRequest $record, array $data) {
                $customer = static::convertQuote($record, $data);
                if ($customer) {
                    Notification::make()->success()->title("Customer ready: {$customer->name}")->send();
                }
            });
    }

    protected static function createShipmentAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('createShipment')
            ->label('Create Shipment')
            ->icon('heroicon-o-truck')->color('primary')
            ->visible(fn (QuoteRequest $record) => static::canConvert() && ! $record->shipment_id && $record->status !== 'closed')
            ->requiresConfirmation()
            ->modalHeading('Create shipment from quote')
            ->modalDescription(fn (QuoteRequest $record) => static::resolutionDescription($record))
            ->form(fn (QuoteRequest $record) => static::resolutionForm($record))
            ->action(function (QuoteRequest $record, array $data, $livewire) {
                if (! static::convertQuote($record, $data)) {
                    return;
                }
                $livewire->redirect(ShipmentResource::getUrl('create', ['quote' => $record->id]));
            });
    }

    /** Runs the conversion; returns null (after notifying) when it cannot proceed. */
    protected static function convertQuote(QuoteRequest $record, array $data): ?\App\Models\Customer
    {
        abort_unless(static::canConvert(), 403);

        try {
            return app(QuoteConversionService::class)->convert($record, $data['resolution'] ?? null);
        } catch (ConfirmationRequired $e) {
            Notification::make()->warning()->title($e->getMessage())->send();
        } catch (\DomainException $e) {
            Notification::make()->danger()->title($e->getMessage())->send();
        }

        return null;
    }

    protected static function resolutionDescription(QuoteRequest $record): HtmlString|string
    {
        if ($record->customer_id) {
            return 'This quote is already linked to a customer.';
        }

        $r = app(QuoteConversionService::class)->resolve($record);
        if ($r['matches']->isEmpty()) {
            return 'No existing customer matches. A new customer will be created from this quote.';
        }
        if (! $r['needs_confirmation']) {
            return 'An existing customer matches this quote and will be linked: ' . $r['matches']->first()->name . '.';
        }

        $lines = $r['conflicts'] ?: ['More than one existing customer matches this quote.'];

        return new HtmlString('Please confirm how to proceed:<ul class="list-disc ml-5 mt-2">'
            . collect($lines)->map(fn ($l) => '<li>' . e($l) . '</li>')->implode('') . '</ul>');
    }

    protected static function resolutionForm(QuoteRequest $record): array
    {
        if ($record->customer_id) {
            return [];
        }

        $r = app(QuoteConversionService::class)->resolve($record);
        if (! $r['needs_confirmation']) {
            return [];
        }

        $options = $r['matches']->mapWithKeys(fn ($c) => [
            "link:{$c->id}" => "Use existing: {$c->name} · {$c->phone}" . ($c->email ? " · {$c->email}" : ''),
        ])->all();
        $options['new'] = 'Create a new customer anyway';

        return [
            Forms\Components\Radio::make('resolution')->label('Customer')->options($options)->required(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListQuoteRequests::route('/'),
            'create' => Pages\CreateQuoteRequest::route('/create'),
            'edit'   => Pages\EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
