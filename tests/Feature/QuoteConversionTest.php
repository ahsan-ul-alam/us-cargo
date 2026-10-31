<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\QuoteRequestResource\Pages\ListQuoteRequests;
use App\Filament\Resources\ShipmentResource\Pages\CreateShipment;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\ShippingRate;
use App\Models\User;
use App\Services\ConfirmationRequired;
use App\Services\QuoteAlreadyShipped;
use App\Services\QuoteConversionService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function quote(array $attrs = []): QuoteRequest
    {
        return QuoteRequest::create($attrs + [
            'name' => 'Rahim Uddin', 'phone' => '+44 7700 900123', 'email' => 'rahim@example.com',
            'weight_kg' => 10, 'direction' => 'uk_to_bd', 'service_type' => 'air_cargo',
            'description' => 'Clothes', 'status' => 'new',
        ]);
    }

    private function service(): QuoteConversionService
    {
        return app(QuoteConversionService::class);
    }

    // ---- phone normalisation ----

    public function test_phone_numbers_are_normalised_across_formats(): void
    {
        $expected = Customer::normalizePhone('07700 900123');
        $this->assertSame('7700900123', $expected);
        $this->assertSame($expected, Customer::normalizePhone('+44 7700-900123'));
        $this->assertSame($expected, Customer::normalizePhone('00447700900123'));
        $this->assertSame('1712345678', Customer::normalizePhone('+880 1712-345678'));
        $this->assertSame('1712345678', Customer::normalizePhone('01712345678'));
    }

    public function test_customer_normalised_phone_is_kept_in_sync(): void
    {
        $c = Customer::create(['name' => 'A', 'phone' => '+44 7700 900123']);
        $this->assertSame('7700900123', $c->phone_normalized);
        $c->update(['phone' => '01712345678']);
        $this->assertSame('1712345678', $c->fresh()->phone_normalized);
    }

    // ---- convert to customer ----

    public function test_convert_creates_customer_and_links_quote(): void
    {
        $quote = $this->quote();
        $customer = $this->service()->convert($quote);

        $this->assertSame('Rahim Uddin', $customer->name);
        $quote->refresh();
        $this->assertSame($customer->id, $quote->customer_id);
        $this->assertSame('converted', $quote->status);
    }

    public function test_repeated_convert_never_creates_duplicate_customers(): void
    {
        $quote = $this->quote();
        $a = $this->service()->convert($quote);
        $b = $this->service()->convert($quote);                // stale in-memory copy, second click
        $c = $this->service()->convert($quote->fresh());

        $this->assertSame($a->id, $b->id);
        $this->assertSame($a->id, $c->id);
        $this->assertSame(1, Customer::count());
    }

    public function test_clean_single_phone_match_links_existing_customer_without_confirmation(): void
    {
        $existing = Customer::create(['name' => 'rahim  uddin', 'phone' => '07700900123', 'email' => 'rahim@example.com']);

        $customer = $this->service()->convert($this->quote());

        $this->assertSame($existing->id, $customer->id);
        $this->assertSame(1, Customer::count());
    }

    public function test_falls_back_to_email_when_phone_does_not_match(): void
    {
        $existing = Customer::create(['name' => 'Rahim Uddin', 'phone' => '07111111111', 'email' => 'RAHIM@example.com']);
        $quote = $this->quote();

        // matched by email, but the phone differs => conflict => must confirm
        $this->assertTrue($this->service()->resolve($quote)['needs_confirmation']);
        $customer = $this->service()->convert($quote, 'link:' . $existing->id);

        $this->assertSame($existing->id, $customer->id);
    }

    public function test_conflicting_name_requires_manual_confirmation(): void
    {
        Customer::create(['name' => 'Someone Else', 'phone' => '+447700900123']);
        $quote = $this->quote();

        $this->assertTrue($this->service()->resolve($quote)['needs_confirmation']);

        $this->expectException(ConfirmationRequired::class);
        $this->service()->convert($quote);
    }

    public function test_multiple_matches_require_manual_confirmation_and_nothing_is_changed(): void
    {
        Customer::create(['name' => 'Rahim Uddin', 'phone' => '07700900123']);
        Customer::create(['name' => 'Rahim Uddin', 'phone' => '+44 7700 900123']);
        $quote = $this->quote();

        try {
            $this->service()->convert($quote);
            $this->fail('Expected ConfirmationRequired');
        } catch (ConfirmationRequired) {
        }

        $this->assertNull($quote->fresh()->customer_id);
        $this->assertSame(2, Customer::count());
    }

    public function test_manual_choice_can_link_or_create_new_and_bad_choice_is_rejected(): void
    {
        $a = Customer::create(['name' => 'Rahim Uddin', 'phone' => '07700900123']);
        Customer::create(['name' => 'Rahim Uddin', 'phone' => '+447700900123']);

        $linked = $this->service()->convert($this->quote(), 'link:' . $a->id);
        $this->assertSame($a->id, $linked->id);

        $created = $this->service()->convert($this->quote(['name' => 'Rahim Uddin']), 'new');
        $this->assertNotSame($a->id, $created->id);
        $this->assertSame(3, Customer::count());

        $this->expectException(ConfirmationRequired::class);
        $this->service()->convert($this->quote(), 'link:99999');   // not one of the matches
    }

    public function test_closed_quote_cannot_be_converted(): void
    {
        $this->expectException(\DomainException::class);
        $this->service()->convert($this->quote(['status' => 'closed']));
    }

    // ---- prefill + pricing ----

    public function test_price_uses_active_rate_and_minimum_weight(): void
    {
        ShippingRate::create(['direction' => 'uk_to_bd', 'service_type' => 'air_cargo', 'price_per_kg' => 6, 'min_weight_kg' => 5, 'currency' => 'GBP', 'is_active' => true]);

        $this->assertSame(60.0, ShippingRate::priceFor('uk_to_bd', 'air_cargo', 10));
        $this->assertSame(30.0, ShippingRate::priceFor('uk_to_bd', 'air_cargo', 2));   // min 5kg charged
        $this->assertNull(ShippingRate::priceFor('bd_to_uk', 'air_cargo', 10));        // no rate
        $this->assertNull(ShippingRate::priceFor('uk_to_bd', 'air_cargo', null));
    }

    public function test_inactive_rate_is_ignored(): void
    {
        ShippingRate::create(['direction' => 'uk_to_bd', 'service_type' => 'parcel', 'price_per_kg' => 9, 'min_weight_kg' => 1, 'currency' => 'GBP', 'is_active' => false]);
        $this->assertNull(ShippingRate::priceFor('uk_to_bd', 'parcel', 3));
    }

    public function test_quote_prefill_maps_countries_customer_and_price(): void
    {
        ShippingRate::create(['direction' => 'bd_to_uk', 'service_type' => 'parcel', 'price_per_kg' => 8, 'min_weight_kg' => 1, 'currency' => 'GBP', 'is_active' => true]);
        $quote = $this->quote(['direction' => 'bd_to_uk', 'service_type' => 'parcel', 'weight_kg' => 3]);
        $customer = $this->service()->convert($quote);
        $customer->update(['address_bd' => 'Dhaka 1207']);

        $p = $this->service()->prefillFromQuote($quote->fresh()->load('customer'));

        $this->assertSame($customer->id, $p['customer_id']);
        $this->assertSame('bd', $p['sender_country']);
        $this->assertSame('uk', $p['recipient_country']);
        $this->assertSame('Dhaka 1207', $p['sender_address']);
        $this->assertSame(24.0, $p['price_gbp']);
        $this->assertSame('pending', $p['status']);
    }

    // ---- shipment creation from quote (once only) ----

    private function shipmentData(array $over = []): array
    {
        return $over + [
            'tracking_number' => Shipment::generateTrackingNumber(),
            'invoice_number' => Shipment::generateInvoiceNumber(),
            'sender_name' => 'Rahim Uddin', 'sender_phone' => '07700900123', 'sender_address' => '1 High St',
            'sender_country' => 'uk', 'recipient_name' => 'Karim', 'recipient_phone' => '01712345678',
            'recipient_address' => 'Dhaka', 'recipient_country' => 'bd',
            'weight_kg' => 10, 'service_type' => 'air_cargo', 'status' => 'pending', 'payment_status' => 'unpaid',
        ];
    }

    public function test_shipment_can_only_be_created_once_per_quote(): void
    {
        $quote = $this->quote();
        $customer = $this->service()->convert($quote);

        $shipment = $this->service()->createShipmentFromQuote($quote->id, $this->shipmentData());
        $this->assertSame($customer->id, $shipment->customer_id);
        $this->assertSame($shipment->id, $quote->fresh()->shipment_id);

        try {
            $this->service()->createShipmentFromQuote($quote->id, $this->shipmentData());
            $this->fail('Expected QuoteAlreadyShipped');
        } catch (QuoteAlreadyShipped) {
        }

        $this->assertSame(1, Shipment::count());
    }

    // ---- Filament UI ----

    public function test_staff_can_convert_from_the_quote_table_and_view_customer(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $quote = $this->quote();

        Livewire::actingAs($staff)->test(ListQuoteRequests::class)
            ->assertTableActionVisible('convertToCustomer', $quote)
            ->assertTableActionHidden('viewCustomer', $quote)
            ->callTableAction('convertToCustomer', $quote)
            ->assertHasNoTableActionErrors()
            ->assertTableActionHidden('convertToCustomer', $quote->fresh())
            ->assertTableActionVisible('viewCustomer', $quote->fresh());

        $this->assertSame(1, Customer::count());
    }

    public function test_conflict_forces_a_choice_in_the_ui(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = Customer::create(['name' => 'Different Name', 'phone' => '07700900123']);
        $quote = $this->quote();

        $test = Livewire::actingAs($admin)->test(ListQuoteRequests::class);

        $test->callTableAction('convertToCustomer', $quote)->assertHasTableActionErrors(['resolution' => 'required']);
        $this->assertNull($quote->fresh()->customer_id);

        $test->callTableAction('convertToCustomer', $quote, ['resolution' => 'link:' . $existing->id])
            ->assertHasNoTableActionErrors();
        $this->assertSame($existing->id, $quote->fresh()->customer_id);
    }

    public function test_create_shipment_action_converts_and_redirects_to_prefilled_form(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $quote = $this->quote();

        Livewire::actingAs($staff)->test(ListQuoteRequests::class)
            ->callTableAction('createShipment', $quote)
            ->assertRedirect(\App\Filament\Resources\ShipmentResource::getUrl('create', ['quote' => $quote->id]));

        $this->assertNotNull($quote->fresh()->customer_id);
    }

    public function test_shipment_form_is_prefilled_from_quote_and_saves_once(): void
    {
        ShippingRate::create(['direction' => 'uk_to_bd', 'service_type' => 'air_cargo', 'price_per_kg' => 6, 'min_weight_kg' => 1, 'currency' => 'GBP', 'is_active' => true]);
        $staff = User::factory()->create(['role' => 'staff']);
        $quote = $this->quote();
        $customer = $this->service()->convert($quote);

        Livewire::actingAs($staff)->withQueryParams(['quote' => $quote->id])->test(CreateShipment::class)
            ->assertFormSet([
                'customer_id' => $customer->id, 'sender_name' => 'Rahim Uddin', 'sender_phone' => '+44 7700 900123',
                'weight_kg' => '10.00', 'price_gbp' => 60.0, 'service_type' => 'air_cargo',
                'sender_country' => 'uk', 'recipient_country' => 'bd',
            ])
            ->fillForm(['sender_address' => '1 High St', 'recipient_name' => 'Karim', 'recipient_phone' => '017', 'recipient_address' => 'Dhaka'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(1, Shipment::count());
        $this->assertSame(Shipment::first()->id, $quote->fresh()->shipment_id);

        // opening the form again for the same quote bounces to the existing shipment
        Livewire::actingAs($staff)->withQueryParams(['quote' => $quote->id])->test(CreateShipment::class)
            ->assertRedirect(\App\Filament\Resources\ShipmentResource::getUrl('view', ['record' => Shipment::first()->id]));
    }

    public function test_stale_second_tab_cannot_create_a_duplicate_shipment(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $quote = $this->quote();
        $this->service()->convert($quote);

        $form = ['sender_address' => '1 High St', 'recipient_name' => 'Karim', 'recipient_phone' => '017', 'recipient_address' => 'Dhaka'];
        $tabA = Livewire::actingAs($staff)->withQueryParams(['quote' => $quote->id])->test(CreateShipment::class)->fillForm($form);
        $tabB = Livewire::actingAs($staff)->withQueryParams(['quote' => $quote->id])->test(CreateShipment::class)->fillForm($form);

        $tabA->call('create');
        $tabB->call('create');

        $this->assertSame(1, Shipment::count());
    }

    public function test_repeat_shipment_prefill_from_customer_profile(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $customer = Customer::create(['name' => 'Repeat Co', 'phone' => '07700900123', 'address_uk' => '9 Mill Rd', 'country' => 'uk']);

        Livewire::actingAs($staff)->test(ListCustomers::class)
            ->assertTableActionVisible('newShipment', $customer);

        Livewire::actingAs($staff)->withQueryParams(['customer' => $customer->id])->test(CreateShipment::class)
            ->assertFormSet(['customer_id' => $customer->id, 'sender_name' => 'Repeat Co', 'sender_address' => '9 Mill Rd', 'sender_country' => 'uk', 'recipient_country' => 'bd'])
            ->fillForm(['recipient_name' => 'X', 'recipient_phone' => '1', 'recipient_address' => 'Y', 'weight_kg' => 2, 'service_type' => 'parcel'])
            ->call('create')->assertHasNoFormErrors();
        Livewire::actingAs($staff)->withQueryParams(['customer' => $customer->id])->test(CreateShipment::class)
            ->fillForm(['recipient_name' => 'X', 'recipient_phone' => '1', 'recipient_address' => 'Y', 'weight_kg' => 2, 'service_type' => 'parcel'])
            ->call('create')->assertHasNoFormErrors();

        $this->assertSame(2, $customer->shipments()->count());   // repeats are allowed
    }

    public function test_bulk_mark_as_contacted_only_moves_new_quotes(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $new = $this->quote();
        $converted = $this->quote(['status' => 'converted']);
        $closed = $this->quote(['status' => 'closed']);

        Livewire::actingAs($staff)->test(ListQuoteRequests::class)
            ->callTableBulkAction('markContacted', [$new, $converted, $closed]);

        $this->assertSame('contacted', $new->fresh()->status);
        $this->assertSame('converted', $converted->fresh()->status);
        $this->assertSame('closed', $closed->fresh()->status);
    }
}
