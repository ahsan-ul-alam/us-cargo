<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PopupResource\Pages;
use App\Models\Popup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PopupResource extends Resource
{
    protected static ?string $model = Popup::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Popups / Offers';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->label('Internal name')->required(),
                Forms\Components\Select::make('type')->label('Popup type')->required()->live()->default('image')
                    ->options([
                        'image' => 'Image only',
                        'image_text' => 'Image + text',
                        'video' => 'Embed video',
                        'text' => 'Text only',
                    ]),
                Forms\Components\TextInput::make('title')
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['image_text', 'video', 'text'])),
                Forms\Components\Textarea::make('body')->rows(3)
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['image_text', 'video', 'text'])),
                Forms\Components\FileUpload::make('image')->image()->disk('public')->directory('popups')
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['image', 'image_text']))
                    ->required(fn (Forms\Get $get) => in_array($get('type'), ['image', 'image_text'])),
                Forms\Components\TextInput::make('video_url')->url()->label('Video link (YouTube/Vimeo/mp4)')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'video')
                    ->required(fn (Forms\Get $get) => $get('type') === 'video'),
                Forms\Components\TextInput::make('button_text'),
                Forms\Components\TextInput::make('button_url')->label('Button / image link')
                    ->helperText('Use a full URL or a path such as /quote'),
                Forms\Components\TextInput::make('delay_seconds')->numeric()->default(3)->minValue(0)->label('Show after (seconds)'),
                Forms\Components\Select::make('frequency')->default('once_per_session')->options([
                    'always' => 'Every page load',
                    'once_per_session' => 'Once per visit',
                    'once_per_day' => 'Once per day',
                ]),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->disk('public'),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
            Tables\Columns\TextColumn::make('starts_at')->dateTime()->toggleable(),
            Tables\Columns\TextColumn::make('ends_at')->dateTime()->toggleable(),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManagePopups::route('/')];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
