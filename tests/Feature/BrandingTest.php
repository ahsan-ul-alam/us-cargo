<?php

namespace Tests\Feature;

use App\Filament\Pages\Branding;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Storage::fake('public');
    }

    public function test_defaults_are_used_until_something_is_uploaded(): void
    {
        $this->assertStringEndsWith('images/pirmecargo.png', SiteSetting::logoUrl());
        $this->assertStringEndsWith('images/favicon.png', SiteSetting::faviconUrl());

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('images/pirmecargo.png', $html);
        $this->assertStringContainsString('images/favicon.png', $html);
    }

    public function test_staff_and_admin_can_open_branding_page(): void
    {
        foreach (['admin', 'staff'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/admin/branding')->assertOk();
        }
    }

    public function test_guests_cannot_open_branding_page(): void
    {
        $this->get('/admin/branding')->assertRedirect();
    }

    public function test_upload_changes_site_and_admin_logo_and_favicon(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        Livewire::actingAs($staff)->test(Branding::class)
            ->fillForm([
                'brand_logo' => UploadedFile::fake()->image('logo.png', 400, 120),
                'brand_favicon' => UploadedFile::fake()->image('fav.png', 64, 64),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $logo = SiteSetting::get('brand_logo');
        $fav = SiteSetting::get('brand_favicon');
        $this->assertNotNull($logo);
        $this->assertNotNull($fav);
        Storage::disk('public')->assertExists($logo);
        Storage::disk('public')->assertExists($fav);

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('storage/' . $logo, $html);
        $this->assertStringContainsString('storage/' . $fav, $html);
        $this->assertStringNotContainsString('images/pirmecargo.png', $html);

        // admin panel (login page shows the brand logo + favicon too)
        auth()->logout();
        $admin = $this->get('/admin/login')->getContent();
        $this->assertStringContainsString('storage/' . $logo, $admin);
        $this->assertStringContainsString('storage/' . $fav, $admin);
    }

    public function test_replacing_a_logo_deletes_the_old_file(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $page = Livewire::actingAs($staff)->test(Branding::class);

        $page->fillForm(['brand_logo' => UploadedFile::fake()->image('a.png')])->call('save');
        $first = SiteSetting::get('brand_logo');

        $page->fillForm(['brand_logo' => UploadedFile::fake()->image('b.png')])->call('save');
        $second = SiteSetting::get('brand_logo');

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_non_images_are_rejected(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        Livewire::actingAs($staff)->test(Branding::class)
            ->fillForm(['brand_logo' => UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')])
            ->call('save')
            ->assertHasFormErrors(['brand_logo']);

        $this->assertNull(SiteSetting::get('brand_logo'));
    }
}
