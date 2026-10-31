<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Hero Video';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Hero Promotional Video';
    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'hero_video_url' => SiteSetting::get('hero_video_url'),
            'hero_video_enabled' => (bool) SiteSetting::get('hero_video_enabled', 1),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Toggle::make('hero_video_enabled')->label('Show video in hero'),
                Forms\Components\TextInput::make('hero_video_url')
                    ->label('Video link')
                    ->url()
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->helperText('YouTube, Vimeo, or a direct .mp4/.webm link. Leave empty to hide the video.'),
            ]),
        ]);
    }

    public function save(): void
    {
        $d = $this->form->getState();
        SiteSetting::set('hero_video_url', $d['hero_video_url'] ?? null);
        SiteSetting::set('hero_video_enabled', ! empty($d['hero_video_enabled']) ? 1 : 0);
        Notification::make()->title('Saved')->success()->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
