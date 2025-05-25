<?php

declare(strict_types=1);

namespace App\Filament\Resources\SoundResource\Pages;

use App\Filament\Resources\SoundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSounds extends ListRecords
{
    protected static string $resource = SoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('bulkUpload')
                ->label('Bulk Upload')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(SoundResource::getUrl('bulk-upload')),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add sound statistics widget here if needed
        ];
    }
}
