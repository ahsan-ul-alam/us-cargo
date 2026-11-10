<?php

namespace Tests\Feature;

use App\Filament\Resources\ShipmentResource\Pages\CreateShipment;
use App\Filament\Resources\ShipmentResource\Pages\ListShipments;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShipmentStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    private function consolidated(array $extra = []): Shipment
    {
        return Shipment::create($extra + [
            'tracking_number' => 'PC-2026-' . random_int(10000, 99999), 'is_consolidated' => true, 'parcel_count' => 2,
            'sender_country' => 'uk', 'recipient_country' => 'bd', 'service_type' => 'air_cargo', 'status' => 'collected',
        ]);
    }

    public function test_air_and_sea_have_their_own_status_flow(): void
    {
        $air = array_keys(Shipment::statusOptions('air'));
        $sea = array_keys(Shipment::statusOptions('sea'));

        foreach (['uk_airport', 'in_air', 'bd_airport', 'sylhet_airport'] as $s) {
            $this->assertContains($s, $air);
            $this->assertNotContains($s, $sea);
        }
        foreach (['uk_port', 'at_sea', 'bd_port'] as $s) {
            $this->assertContains($s, $sea);
            $this->assertNotContains($s, $air);
        }
        $this->assertSame('Chattogram International Port', Shipment::statusOptions('sea')['bd_port']);
    }

    public function test_create_form_defaults_to_air_and_switches_options_to_sea(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $t = Livewire::test(CreateShipment::class);
        $this->assertArrayHasKey('uk_airport', $t->instance()->form->getComponent('data.status')->getOptions());

        $t->set('data.is_sea', true);
        $opts = $t->instance()->form->getComponent('data.status')->getOptions();
        $this->assertArrayHasKey('at_sea', $opts);
        $this->assertArrayNotHasKey('uk_airport', $opts);

        $t->fillForm(['is_consolidated' => true, 'parcel_count' => 3, 'service_type' => 'air_cargo',
            'sender_country' => 'uk', 'recipient_country' => 'bd', 'status' => 'bd_port'])
            ->call('create')->assertHasNoFormErrors();

        $sea = Shipment::latest('id')->first();
        $this->assertSame('sea', $sea->transport_mode);
        $this->assertSame('bd_port', $sea->status);
        $this->assertSame('চট্টগ্রাম আন্তর্জাতিক বন্দর', $sea->status_label);
    }

    public function test_add_tracking_popup_can_switch_a_shipment_to_sea(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $shipment = $this->consolidated();

        Livewire::test(ListShipments::class)
            ->callTableAction('addTracking', $shipment, data: [
                'is_sea' => true, 'shipment_status' => 'at_sea', 'status' => 'In Sea',
                'description' => 'Vessel departed', 'occurred_at' => now(),
            ])->assertHasNoTableActionErrors();

        $shipment->refresh();
        $this->assertSame('sea', $shipment->transport_mode);
        $this->assertSame('at_sea', $shipment->status);
    }
}
