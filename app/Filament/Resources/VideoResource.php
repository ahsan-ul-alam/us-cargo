<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Models\Video;
use App\Support\VideoEmbed;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;
    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Videos';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(150)->columnSpanFull(),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\Select::make('source')->label('Video source')->required()->live()->default('link')
                    ->options(['link' => 'Link (YouTube / Vimeo / Google Drive / mp4)', 'upload' => 'Upload a file'])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('video_url')->label('Video link')->url()
                    ->helperText('YouTube, Vimeo, Google Drive (share as "Anyone with the link") or a direct .mp4 link.')
                    ->visible(fn (Forms\Get $get) => $get('source') === 'link')
                    ->required(fn (Forms\Get $get) => $get('source') === 'link')
                    ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                        if ($value && VideoEmbed::parse($value) === null) {
                            $fail('This link is not a supported YouTube, Vimeo, Google Drive or .mp4 video link.');
                        }
                    })
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('video_file')->label('Video file')
                    ->disk('public')->directory('videos')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                    ->maxSize(512 * 1024)
                    ->helperText('MP4 / WebM / OGG. Large files also depend on the server upload limit (upload_max_filesize / post_max_size).')
                    ->visible(fn (Forms\Get $get) => $get('source') === 'upload')
                    ->required(fn (Forms\Get $get) => $get('source') === 'upload')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('thumbnail')->image()->disk('public')->directory('videos/thumbs')
                    ->helperText('Optional. YouTube links use their own thumbnail automatically.'),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->helperText('Lower numbers appear first.'),
                    Forms\Components\Toggle::make('is_active')->label('Visible on website')->default(true),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('thumbnail')->disk('public'),
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('source')->badge(),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
            Tables\Columns\ToggleColumn::make('is_active')->label('Visible'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageVideos::route('/')];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
