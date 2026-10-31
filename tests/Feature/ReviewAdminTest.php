<?php

namespace Tests\Feature;

use App\Filament\Resources\ReviewResource\Pages\ManageReviews;
use App\Models\Review;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Review::query()->delete();   // the migration seeds 3 starter reviews
    }

    private function review(string $name, bool $approved = false): Review
    {
        return Review::create(['name' => $name, 'rating' => 5, 'body' => 'ok', 'is_approved' => $approved]);
    }

    private function page()
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return Livewire::actingAs(User::factory()->create(['role' => 'admin']))->test(ManageReviews::class);
    }

    public function test_bulk_delete(): void
    {
        [$a, $b, $keep] = [$this->review('A'), $this->review('B'), $this->review('Keep')];

        $this->page()->callTableBulkAction('delete', [$a, $b]);

        $this->assertSame([$keep->id], Review::pluck('id')->all());
    }

    public function test_bulk_approve_and_hide(): void
    {
        [$a, $b] = [$this->review('A'), $this->review('B')];

        $page = $this->page();
        $page->callTableBulkAction('approve', [$a, $b]);
        $this->assertSame(2, Review::where('is_approved', true)->count());

        $page->callTableBulkAction('unapprove', [$a]);
        $this->assertFalse($a->fresh()->is_approved);
        $this->assertTrue($b->fresh()->is_approved);
    }

    public function test_reviewer_without_photo_gets_a_visible_initial_avatar(): void
    {
        $this->page()->assertSuccessful();   // table renders, incl. the default avatar closure
        $this->review('রিয়াদ');
        $this->page()->assertSuccessful()->assertSeeHtml('data:image/svg+xml;base64,');
    }
}
