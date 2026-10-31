<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\Counters;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CounterSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Counters';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Site Counters';
    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            collect(Counters::GROUPS)->mapWithKeys(fn ($l, $g) => [$g => Counters::get($g)])->all()
            + Counters::promo()
        );
    }

    public function form(Form $form): Form
    {
        $sections = [];
        foreach (Counters::GROUPS as $group => $title) {
            $sections[] = Forms\Components\Section::make($title)->schema([
                Forms\Components\Repeater::make($group)
                    ->hiddenLabel()
                    ->schema([
                        Forms\Components\TextInput::make('value')->label('Number / value')->required()->maxLength(40),
                        Forms\Components\TextInput::make('label')->label('Label')->required()->maxLength(120),
                    ])
                    ->columns(2)
                    ->reorderable()
                    ->addActionLabel('Add counter')
                    ->minItems(1)
                    ->maxItems(6),
            ]);
        }

        $sections[] = Forms\Components\Section::make('Promo badge (fixed, top-right)')->schema([
            Forms\Components\Toggle::make('promo_enabled')->label('Show promo badge'),
            Forms\Components\TextInput::make('promo_tag')->label('Small top text')->maxLength(40),
            Forms\Components\TextInput::make('promo_value')->label('Main text / number')->maxLength(40),
            Forms\Components\TextInput::make('promo_cta')->label('Bottom text')->maxLength(60),
        ])->columns(2);

        return $form->statePath('data')->schema($sections);
    }

    public function save(): void
    {
        $d = $this->form->getState();
        foreach (Counters::PROMO_DEFAULTS as $k => $v) {
            SiteSetting::set($k, $k === 'promo_enabled' ? (empty($d[$k]) ? 0 : 1) : ($d[$k] ?? ''));
        }
        foreach (array_keys(Counters::GROUPS) as $group) {
            Counters::save($group, $d[$group] ?? []);
        }
        Notification::make()->title('Saved')->success()->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
