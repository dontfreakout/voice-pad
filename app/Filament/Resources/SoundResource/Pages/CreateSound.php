<?php

declare(strict_types=1);

namespace App\Filament\Resources\SoundResource\Pages;

use App\Filament\Resources\SoundResource;
use App\Services\SoundService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

final class CreateSound extends CreateRecord
{
    protected static string $resource = SoundResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Handle file upload if present
        if (isset($data['audio_file']) && $data['audio_file'] instanceof UploadedFile) {
            $uploadedFile = $data['audio_file'];

            // Create sound using the service
            return app(SoundService::class)->storeSoundFile($uploadedFile, $data);
        }

        // If no file or file is not UploadedFile, create normally
        // Remove audio_file from data since it's not a database field
        unset($data['audio_file']);

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->getModel();

        return $modelClass::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }
}
