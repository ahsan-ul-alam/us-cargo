<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class Branding extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Branding';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Branding';
    protected static ?string $slug = 'branding';
    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageBranding() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'brand_logo' => SiteSetting::get('brand_logo'),
            'brand_favicon' => SiteSetting::get('brand_favicon'),
            'brand_primary' => SiteSetting::get('brand_primary', SiteSetting::BRAND_PRIMARY),
            'brand_secondary' => SiteSetting::get('brand_secondary', SiteSetting::BRAND_SECONDARY),
            'hero_overlay' => (int) SiteSetting::get('hero_overlay', 85),
            'hero_overlay_color' => SiteSetting::get('hero_overlay_color') ?: SiteSetting::get('brand_primary', SiteSetting::BRAND_PRIMARY),
            'footer_logo' => SiteSetting::get('footer_logo'),
            'footer_text_color' => SiteSetting::get('footer_text_color') ?: '#d5dbe6',
            'site_name' => SiteSetting::siteName(),
            'site_legal_name' => SiteSetting::legalName(),
            'site_title' => SiteSetting::siteTitle(),
            'site_description' => SiteSetting::siteDescription(),
        ]);
    }

    public function form(Form $form): Form
    {
        $types = ['image/png', 'image/jpeg', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];

        return $form->statePath('data')->schema([
            Forms\Components\Section::make('Site identity')
                ->description('Name and title used across the public website.')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('site_name')
                        ->label('Site name')->required()->maxLength(80)
                        ->helperText('Used in the browser tab (when a page has no title), logo alt text and the home page intro.'),
                    Forms\Components\TextInput::make('site_legal_name')
                        ->label('Footer / legal name')->maxLength(120)
                        ->helperText('Shown in the footer copyright line, e.g. "Prime Cargo Ltd".'),
                    Forms\Components\TextInput::make('site_title')
                        ->label('Site title (browser tab suffix)')->maxLength(120)
                        ->helperText('Appears after the page title, e.g. "Home — UK ⇄ Bangladesh". Leave empty for none.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('site_description')
                        ->label('Default meta description')->rows(2)->maxLength(300)
                        ->helperText('Shown in search results for pages without their own description.')
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Logo')
                ->description('Shown in the website header, the mobile menu, and the admin panel / login page. Leave empty to use the default logo.')
                ->schema([
                    Forms\Components\FileUpload::make('brand_logo')
                        ->label('Logo')
                        ->image()->imageEditor()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                        ->disk('public')->directory('branding')->visibility('public')
                        ->maxSize(2048)
                        ->helperText('PNG, JPG or WebP, up to 2 MB. A transparent PNG about 400 px wide works best.'),
                ]),
            Forms\Components\Section::make('Footer')
                ->description('Logo and text color of the website footer (the footer background uses the primary color).')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('footer_logo')
                        ->label('Footer logo')
                        ->image()
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                        ->disk('public')->directory('branding')->visibility('public')
                        ->maxSize(2048)
                        ->helperText('Optional. Use a light/white logo that is visible on the dark footer. Leave empty to use the main logo.'),
                    Forms\Components\ColorPicker::make('footer_text_color')
                        ->label('Footer text color')
                        ->helperText('Color of footer text and links. Default #d5dbe6.'),
                ]),
            Forms\Components\Section::make('Favicon')
                ->description('The small icon in the browser tab. Leave empty to use the default.')
                ->schema([
                    Forms\Components\FileUpload::make('brand_favicon')
                        ->label('Favicon')
                        ->acceptedFileTypes($types)
                        ->disk('public')->directory('branding')->visibility('public')
                        ->maxSize(1024)
                        ->helperText('Square PNG or ICO, at least 64×64 px, up to 1 MB.'),
                ]),
            Forms\Components\Section::make('Colors')
                ->description('Brand colors used across the public website. Hover shades and soft tints are generated automatically.')
                ->columns(2)
                ->schema([
                    Forms\Components\ColorPicker::make('brand_primary')
                        ->label('Primary color')
                        ->helperText('Headings, dark sections, navy buttons. Default #022450.')
                        ->required(),
                    Forms\Components\ColorPicker::make('brand_secondary')
                        ->label('Secondary color')
                        ->helperText('Accents, main buttons, highlights. Default #fd9230.')
                        ->required(),
                    Forms\Components\ColorPicker::make('hero_overlay_color')
                        ->label('Hero overlay color')
                        ->helperText('Color of the gradient over the hero image. Defaults to the primary color.'),
                    Forms\Components\TextInput::make('hero_overlay')
                        ->label('Hero overlay strength (%)')
                        ->numeric()->minValue(0)->maxValue(100)->step(5)->suffix('%')->default(85)->required()
                        ->helperText('How strong the primary-color gradient is over the hero image. 0 = no overlay (image only), 100 = fully solid at its strongest edge. Default 85.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();

        SiteSetting::set('footer_text_color', (string) ($data['footer_text_color'] ?? ''));

        foreach (['brand_logo', 'brand_favicon', 'footer_logo'] as $key) {
            $old = SiteSetting::get($key);
            $new = $data[$key] ?? null;

            if ($old && $old !== $new) {
                Storage::disk('public')->delete($old);   // don't leave replaced files behind
            }
            SiteSetting::set($key, $new);
        }

        SiteSetting::set('brand_primary', $data['brand_primary'] ?? SiteSetting::BRAND_PRIMARY);
        SiteSetting::set('brand_secondary', $data['brand_secondary'] ?? SiteSetting::BRAND_SECONDARY);

        $hc = (string) ($data['hero_overlay_color'] ?? '');
        $primary = (string) ($data['brand_primary'] ?? SiteSetting::BRAND_PRIMARY);
        // Storing nothing keeps the overlay following the primary color.
        SiteSetting::set('hero_overlay_color', strcasecmp($hc, $primary) === 0 ? '' : $hc);

        SiteSetting::set('hero_overlay', max(0, min(100, (int) ($data['hero_overlay'] ?? 85))));

        foreach (['site_name', 'site_legal_name', 'site_title', 'site_description'] as $key) {
            SiteSetting::set($key, trim((string) ($data[$key] ?? '')));
        }

        Notification::make()->title('Branding saved')->success()->send();
    }
}
