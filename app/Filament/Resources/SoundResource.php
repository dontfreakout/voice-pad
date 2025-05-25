<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SoundResource\Pages;
use App\Models\Sound;
use App\Services\SoundService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SoundResource extends Resource
{
    protected static ?string $model = Sound::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Sound Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralModelLabel = 'Sounds';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('name',
                                $state ? ucfirst($state) : ''))
                            ->columnSpan(2),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->rows(3)
                                    ->maxLength(1000),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Audio File')
                    ->schema([
                        Forms\Components\FileUpload::make('audio_file')
                            ->label('Sound File')
                            ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/mp4', 'audio/wav', 'audio/x-wav'])
                            ->maxSize(10240) // 10MB
                            ->required(fn (string $context): bool => $context === 'create')
                            ->storeFiles(false) // Prevent auto-storage
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if ($state instanceof \Illuminate\Http\UploadedFile) {
                                    $set('detected_duration', round($state->getSize() / 16000, 2));
                                }
                            })
                            ->helperText(fn (string $context,
                            ): string => $context === 'edit' ? 'Upload a new file to replace the current one' : 'Upload an audio file')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('current_file_info')
                            ->label('Current File Information')
                            ->content(function ($record): HtmlString|string {
                                if (! $record) {
                                    return 'No file uploaded yet';
                                }

                                /** @var Sound $record */
                                return new HtmlString("
                                    <div class='space-y-1 text-sm'>
                                        <div><strong>Original Name:</strong> {$record->file_name}</div>
                                        <div><strong>Size:</strong> {$record->formatted_file_size}</div>
                                        <div><strong>Duration:</strong> {$record->formatted_duration}</div>
                                        <div><strong>Type:</strong> {$record->mime_type}</div>
                                        <div class='mt-2'>
                                            <audio controls preload='metadata' class='w-full max-w-md'>
                                                <source src='{$record->file_url}' type='{$record->mime_type}'>
                                                Your browser does not support the audio element.
                                            </audio>
                                        </div>
                                    </div>
                                ");
                            })
                            ->visible(fn ($record): bool => $record && $record->exists)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first within the category'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextInputColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Sound Name'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Duration')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('duration', $direction);
                    }),

                Tables\Columns\TextColumn::make('formatted_file_size')
                    ->label('File Size')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('file_size', $direction);
                    }),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('play')
                    ->label('Play')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(function ($record): void {
                        // This will be handled by JavaScript on the frontend
                    })
                    ->openUrlInNewTab(false)
                    ->url(fn (Sound $record): string => $record->file_url)
                    ->extraAttributes(fn (Sound $record): array => [
                        'class' => 'play-sound-btn',
                        'data-sound-url' => $record->file_url,
                    ]),

                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function (Sound $record): void {
                        app(SoundService::class)->deleteSound($record);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Support\Collection $records): void {
                            $soundService = app(SoundService::class);
                            /** @var Sound $record */
                            foreach ($records as $record) {
                                $soundService->deleteSound($record);
                            }
                        }),
                ]),
            ])
            ->defaultSort('category.name')
            ->recordUrl(null)
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Disable reordering' : 'Enable reordering'),
            )->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('New Sound')
                    ->url(self::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSounds::route('/'),
            'create' => Pages\CreateSound::route('/create'),
            'bulk-upload' => Pages\BulkUploadSounds::route('/bulk-upload'),
            'view' => Pages\ViewSound::route('/{record}'),
            'edit' => Pages\EditSound::route('/{record}/edit'),
        ];
    }
}
