<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactMessageResource\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_contact_messages_can_be_bulk_deleted(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $make = fn ($n) => ContactMessage::create(['name' => $n, 'phone' => '1', 'message' => 'hi', 'status' => 'unread']);
        [$a, $b, $keep] = [$make('A'), $make('B'), $make('Keep')];

        Livewire::actingAs(User::factory()->create(['role' => 'staff']))
            ->test(ListContactMessages::class)
            ->callTableBulkAction('delete', [$a, $b]);

        $this->assertSame([$keep->id], ContactMessage::pluck('id')->all());
    }
}
