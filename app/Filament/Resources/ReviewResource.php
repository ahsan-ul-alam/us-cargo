<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Reviews';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->columns(2)->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('location'),
                Forms\Components\Select::make('rating')->required()->default(5)
                    ->options([5 => '5 stars', 4 => '4 stars', 3 => '3 stars', 2 => '2 stars', 1 => '1 star']),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Textarea::make('body')->label('Review')->required()->columnSpanFull(),
                Forms\Components\FileUpload::make('photo')->image()->avatar()->disk('public')->directory('reviews'),
                Forms\Components\Toggle::make('is_approved')->label('Show on website')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('photo')->disk('public')->circular()->size(40)
                ->defaultImageUrl(fn (Review $record) => static::initialAvatar($record->name)),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('location'),
            Tables\Columns\TextColumn::make('rating')->formatStateUsing(fn ($state) => str_repeat('★', (int) $state)),
            Tables\Columns\TextColumn::make('body')->limit(60),
            Tables\Columns\ToggleColumn::make('is_approved')->label('Approved'),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\TernaryFilter::make('is_approved')->label('Approved'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\BulkAction::make('approve')
                    ->label('Approve selected')->icon('heroicon-o-check-circle')->color('success')
                    ->action(fn (Collection $records) => $records->each->update(['is_approved' => true]))
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('unapprove')
                    ->label('Hide selected')->icon('heroicon-o-eye-slash')
                    ->action(fn (Collection $records) => $records->each->update(['is_approved' => false]))
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])->defaultSort('created_at', 'desc');
    }

    /** Round coloured avatar with the reviewer's initial, used when no photo was uploaded. */
    protected static function initialAvatar(?string $name): string
    {
        $initial = htmlspecialchars(mb_strtoupper(mb_substr(trim((string) $name), 0, 1)) ?: '?', ENT_QUOTES);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80"><rect width="80" height="80" fill="#fff4e9"/>'
            . '<text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Arial,sans-serif" font-size="36" font-weight="700" fill="#ef7d15">' . $initial . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageReviews::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $n = Review::where('is_approved', false)->count();
        return $n ?: null;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
