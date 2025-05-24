<?php

declare(strict_types=1);

namespace App\Filament\Resources\SoundResource\Pages;

use App\Filament\Resources\SoundResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewSound extends ViewRecord
{
    protected static string $resource = SoundResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Sound Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->weight('bold')
                            ->size('lg'),

                        Infolists\Components\TextEntry::make('category.name')
                            ->label('Category')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Audio Player')
                    ->schema([
                        Infolists\Components\TextEntry::make('audio_player')
                            ->label('')
                            ->formatStateUsing(fn ($record): HtmlString => new HtmlString("
                                <div class='audio-player-container'>
                                    <audio controls preload='metadata' class='w-full'>
                                        <source src='{$record->file_url}' type='{$record->mime_type}'>
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            "))
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('File Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('file_name')
                            ->label('Original Filename'),

                        Infolists\Components\TextEntry::make('formatted_duration')
                            ->label('Duration'),

                        Infolists\Components\TextEntry::make('formatted_file_size')
                            ->label('File Size'),

                        Infolists\Components\TextEntry::make('mime_type')
                            ->label('File Type'),

                        Infolists\Components\TextEntry::make('sort_order')
                            ->label('Sort Order'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Uploaded')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
