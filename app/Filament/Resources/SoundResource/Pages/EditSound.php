<?php

declare(strict_types=1);

namespace App\Filament\Resources\SoundResource\Pages;

use App\Filament\Resources\SoundResource;
use App\Services\SoundService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Http\UploadedFile;

class EditSound extends EditRecord
{
    protected static string $resource = SoundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->action(function (): void {
                    if (! $this->record instanceof \App\Models\Sound) {
                        return;
                    }

                    app(SoundService::class)->deleteSound($this->record);
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Handle file replacement if a new file is uploaded
        if (isset($data['audio_file']) && $data['audio_file'] instanceof UploadedFile) {
            $uploadedFile = $data['audio_file'];
            $soundService = app(SoundService::class);

            // Update sound using the service
            /** @var \App\Models\Sound $record */
            return $soundService->updateSoundFile($record, $uploadedFile, $data);
        }

        // Update without file change
        // Remove audio_file from data since it's not a database field
        unset($data['audio_file']);

        $record->update($data);

        return $record;
    }
}
