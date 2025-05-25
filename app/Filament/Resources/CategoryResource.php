<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Sound Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $pluralModelLabel = 'Categories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (?string $state, callable $set, callable $get): void {
                        if ($state) {
                            $set('name', ucfirst($state));
                            $currentSlug = $get('slug');
                            if (empty($currentSlug) || $currentSlug === Str::slug($get('name'))) {
                                // Model handles robust slug generation
                            }
                        }
                    }),

                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->unique(table: Category::class, column: 'slug', ignoreRecord: true)
                    ->helperText('If left empty, the slug will be generated automatically from the name. Manually entered slugs will also be formatted.')
                    ->afterStateUpdated(function (callable $set, $state): void {
                        if ($state !== null) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->dehydrated(fn ($state) => filled($state))
                    ->placeholder('my-category-slug'),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = (string) $column->getState();
                        if (mb_strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('sounds_count')
                    ->counts('sounds')
                    ->label('Sounds')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
