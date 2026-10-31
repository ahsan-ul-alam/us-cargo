<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\ShippingRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuoteConversionService
{
    /** Existing customers that look like the quote's sender: phone first, then email. */
    public function matches(QuoteRequest $quote): Collection
    {
        $phone = Customer::normalizePhone($quote->phone);
        if ($phone !== '') {
            $byPhone = Customer::where('phone_normalized', $phone)->get();
            if ($byPhone->isNotEmpty()) {
                return $byPhone;
            }
        }

        $email = mb_strtolower(trim((string) $quote->email));

        return $email !== ''
            ? Customer::whereRaw('lower(email) = ?', [$email])->get()
            : collect();
    }

    /** Human-readable differences between the quote and a matched customer. */
    public function conflicts(QuoteRequest $quote, Customer $customer): array
    {
        $out = [];
        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        if ($norm($quote->name) !== $norm($customer->name)) {
            $out[] = "Name differs: \"{$customer->name}\" (customer) vs \"{$quote->name}\" (quote)";
        }
        if (Customer::normalizePhone($quote->phone) !== $customer->phone_normalized) {
            $out[] = "Phone differs: {$customer->phone} (customer) vs {$quote->phone} (quote) — matched by email";
        }
        if ($quote->email && $customer->email && $norm($quote->email) !== $norm($customer->email)) {
            $out[] = "Email differs: {$customer->email} (customer) vs {$quote->email} (quote)";
        }

        return $out;
    }

    /** @return array{matches: Collection, conflicts: array, needs_confirmation: bool} */
    public function resolve(QuoteRequest $quote): array
    {
        $matches = $this->matches($quote);
        $conflicts = $matches->count() === 1 ? $this->conflicts($quote, $matches->first()) : [];

        return [
            'matches' => $matches,
            'conflicts' => $conflicts,
            'needs_confirmation' => $matches->count() > 1 || $conflicts !== [],
        ];
    }

    /**
     * Link (or create) the customer for a quote. Idempotent: a quote that already has a
     * customer returns it untouched, so repeated clicks never create duplicates.
     *
     * @param  string|null  $resolution  "link:{customerId}" or "new" — required only when resolve() asks for confirmation
     *
     * @throws ConfirmationRequired
     */
    public function convert(QuoteRequest $quote, ?string $resolution = null): Customer
    {
        return DB::transaction(function () use ($quote, $resolution) {
            $quote = QuoteRequest::whereKey($quote->getKey())->lockForUpdate()->firstOrFail();

            if ($quote->customer_id && $quote->customer) {
                return $quote->customer;
            }
            if ($quote->status === 'closed') {
                throw new \DomainException('A closed quote cannot be converted.');
            }

            $r = $this->resolve($quote);

            if ($r['matches']->isEmpty()) {
                $customer = $this->createCustomer($quote);
            } elseif (! $r['needs_confirmation']) {
                $customer = $r['matches']->first();
            } elseif ($resolution === 'new') {
                $customer = $this->createCustomer($quote);
            } elseif (str_starts_with((string) $resolution, 'link:')
                && ($picked = $r['matches']->firstWhere('id', (int) substr($resolution, 5)))) {
                $customer = $picked;
            } else {
                throw new ConfirmationRequired('Please choose which customer to use.');
            }

            $quote->update(['customer_id' => $customer->id, 'status' => 'converted']);

            return $customer;
        });
    }

    protected function createCustomer(QuoteRequest $quote): Customer
    {
        return Customer::create([
            'name' => $quote->name,
            'email' => $quote->email,
            'phone' => $quote->phone,
            'country' => $quote->direction === 'bd_to_uk' ? 'bd' : 'uk',
            'notes' => "Created from quote #{$quote->id}",
        ]);
    }

    /** Form defaults for a new shipment based on a (converted) quote. */
    public function prefillFromQuote(QuoteRequest $quote): array
    {
        $customer = $quote->customer;
        $senderCountry = $quote->direction === 'bd_to_uk' ? 'bd' : 'uk';

        return [
            'customer_id' => $quote->customer_id,
            'sender_name' => $quote->name,
            'sender_phone' => $quote->phone,
            'sender_address' => $customer ? $this->addressFor($customer, $senderCountry) : null,
            'sender_country' => $senderCountry,
            'recipient_country' => $senderCountry === 'uk' ? 'bd' : 'uk',
            'service_type' => $quote->service_type,
            'weight_kg' => $quote->weight_kg,
            'price_gbp' => ShippingRate::priceFor($quote->direction, $quote->service_type, $quote->weight_kg),
            'description' => $quote->description,
            'status' => 'pending',
        ];
    }

    /** Form defaults for a repeat shipment started from a customer profile. */
    public function prefillFromCustomer(Customer $customer): array
    {
        $senderCountry = $customer->country === 'bd' ? 'bd' : 'uk';

        return [
            'customer_id' => $customer->id,
            'sender_name' => $customer->name,
            'sender_phone' => $customer->phone,
            'sender_address' => $this->addressFor($customer, $senderCountry),
            'sender_country' => $senderCountry,
            'recipient_country' => $senderCountry === 'uk' ? 'bd' : 'uk',
            'status' => 'pending',
        ];
    }

    protected function addressFor(Customer $customer, string $country): ?string
    {
        return $country === 'bd' ? $customer->address_bd : $customer->address_uk;
    }

    /**
     * Create the shipment for a quote exactly once. The quote row is locked, so a second
     * click / tab / request sees shipment_id already set and is rejected.
     *
     * @throws QuoteAlreadyShipped
     */
    public function createShipmentFromQuote(int $quoteId, array $data): Shipment
    {
        return DB::transaction(function () use ($quoteId, $data) {
            $quote = QuoteRequest::whereKey($quoteId)->lockForUpdate()->firstOrFail();

            if ($quote->shipment_id) {
                throw new QuoteAlreadyShipped('This quote already has a shipment.');
            }

            $shipment = Shipment::create($data + ['customer_id' => $quote->customer_id]);
            $quote->update(['shipment_id' => $shipment->id, 'status' => 'converted']);

            return $shipment;
        });
    }
}
