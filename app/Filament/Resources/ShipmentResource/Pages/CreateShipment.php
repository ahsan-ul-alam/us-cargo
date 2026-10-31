<?php

namespace App\Filament\Resources\ShipmentResource\Pages;

use App\Filament\Resources\ShipmentResource;
use App\Filament\Resources\QuoteRequestResource;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Services\QuoteAlreadyShipped;
use App\Services\QuoteConversionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;

class CreateShipment extends CreateRecord
{
    protected static string $resource = ShipmentResource::class;

    #[Url(as: 'quote')]
    public ?int $fromQuote = null;

    #[Url(as: 'customer')]
    public ?int $fromCustomer = null;

    public function mount(): void
    {
        if ($this->fromQuote) {
            $quote = QuoteRequest::find($this->fromQuote);

            if (! $quote || ! $quote->customer_id) {
                Notification::make()->warning()->title('Convert the quote to a customer first.')->send();
                $this->redirect(QuoteRequestResource::getUrl('index'));

                return;
            }
            if ($quote->shipment_id) {
                Notification::make()->warning()->title('This quote already has a shipment.')->send();
                $this->redirect(ShipmentResource::getUrl('view', ['record' => $quote->shipment_id]));

                return;
            }
        }

        parent::mount();
    }

    protected function fillForm(): void
    {
        parent::fillForm();

        $service = app(QuoteConversionService::class);
        $prefill = [];

        if ($this->fromQuote && ($quote = QuoteRequest::with('customer')->find($this->fromQuote))) {
            $prefill = $service->prefillFromQuote($quote);
        } elseif ($this->fromCustomer && ($customer = Customer::find($this->fromCustomer))) {
            $prefill = $service->prefillFromCustomer($customer);
        }

        if ($prefill) {
            $this->form->fill(array_merge($this->form->getRawState(), array_filter($prefill, fn ($v) => $v !== null)));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        if ($this->fromQuote) {
            abort_unless(auth()->user()?->canConvertQuotes(), 403);

            try {
                return app(QuoteConversionService::class)->createShipmentFromQuote($this->fromQuote, $data);
            } catch (QuoteAlreadyShipped $e) {
                Notification::make()->warning()->title($e->getMessage())->send();
                $this->halt();
            }
        }

        return parent::handleRecordCreation($data);
    }
}
