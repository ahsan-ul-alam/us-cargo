<?php

namespace Tests\Feature;

use App\Filament\Widgets\LatestShipments;
use App\Filament\Widgets\NewQuoteRequests;
use App\Filament\Widgets\ShipmentsChart;
use App\Filament\Widgets\ShipmentStatusChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\UnreadMessages;
use App\Models\ContactMessage;
use App\Models\QuoteRequest;
use App\Models\Shipment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedData(): void
    {
        Shipment::create([
            'tracking_number' => 'PC-2026-00001', 'sender_name' => 'S', 'sender_phone' => '1', 'sender_address' => 'a',
            'sender_country' => 'uk', 'recipient_name' => 'R', 'recipient_phone' => '2', 'recipient_address' => 'b',
            'recipient_country' => 'bd', 'weight_kg' => 5, 'price_gbp' => 30, 'paid_amount' => 10, 'paid_at' => now(),
            'service_type' => 'air_cargo', 'status' => 'in_transit', 'payment_status' => 'partial',
        ]);
        QuoteRequest::create(['name' => 'Q', 'phone' => '3', 'direction' => 'uk_to_bd', 'service_type' => 'parcel', 'status' => 'new']);
        ContactMessage::create(['name' => 'M', 'phone' => '4', 'message' => 'hello', 'status' => 'unread']);
    }

    public function test_dashboard_page_loads_with_greeting(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff', 'name' => 'Sam']))
            ->get('/admin')->assertOk()->assertSee('Sam');
    }

    public function test_every_widget_renders_with_and_without_data(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create(['role' => 'admin']);
        $widgets = [StatsOverview::class, ShipmentsChart::class, ShipmentStatusChart::class,
            LatestShipments::class, NewQuoteRequests::class, UnreadMessages::class];

        foreach ($widgets as $w) {                       // empty database
            Livewire::actingAs($user)->test($w)->assertSuccessful();
        }

        $this->seedData();
        foreach ($widgets as $w) {                       // with data
            Livewire::actingAs($user)->test($w)->assertSuccessful();
        }

        Livewire::actingAs($user)->test(LatestShipments::class)->assertSee('PC-2026-00001');
        Livewire::actingAs($user)->test(NewQuoteRequests::class)->assertSee('Q');
        Livewire::actingAs($user)->test(UnreadMessages::class)->assertSee('hello');
        Livewire::actingAs($user)->test(StatsOverview::class)->assertSee('£10.00')->assertSee('£20.00');
    }
}
