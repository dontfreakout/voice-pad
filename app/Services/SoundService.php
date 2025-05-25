<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Sound;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kiwilan\Audio\Audio;

class SoundService
{
    public const DISK_NAME = 'public';

    /**
     * Store an uploaded sound file and create a Sound record.
     *
     * @param  array<string, mixed>  $data
     */
    public function storeSoundFile(UploadedFile $file, array $data): Sound
    {
        $originalName = $file->getClientOriginalName();
        $filename = $this->generateUniqueFilename($originalName);

        // Store file in public/sounds directory
        $filePath = $this->storeFile($file, $filename);

        // Get file information
        $duration = $this->extractAudioDuration($file);

        // Ensure category_id is an integer
        $categoryId = (int) $data['category_id'];

        // Create sound record
        return Sound::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'file_path' => $filePath,
            'file_name' => $originalName,
            'mime_type' => $file->getMimeType() ?? 'audio/mpeg',
            'file_size' => $file->getSize(),
            'duration' => $duration,
            'category_id' => $categoryId,
            'sort_order' => $this->getNextSortOrder($categoryId),
        ]);
    }

    /**
     * Create a Sound record from an uploaded file and basic details.
     * Used by the bulk uploader.
     */
    public function createSoundFromFile(UploadedFile $file, string $name, int $categoryId, ?string $description): Sound
    {
        $originalName = $file->getClientOriginalName();
        $filename = $this->generateUniqueFilename($originalName);
        $filePath = $this->storeFile($file, $filename);
        $duration = $this->extractAudioDuration($file);

        return Sound::create([
            'name' => $name,
            'description' => $description,
            'file_path' => $filePath,
            'file_name' => $originalName,
            'mime_type' => $file->getMimeType() ?? 'audio/mpeg',
            'file_size' => $file->getSize(),
            'duration' => $duration,
            'category_id' => $categoryId,
            'sort_order' => $this->getNextSortOrder($categoryId),
        ]);
    }

    /**
     * Update sound file if a new file is uploaded.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSoundFile(Sound $sound, ?UploadedFile $file = null, array $data = []): Sound
    {
        // Ensure category_id is an integer if provided
        $categoryId = isset($data['category_id']) ? (int) $data['category_id'] : $sound->category_id;

        $updateData = [
            'name' => $data['name'] ?? $sound->name,
            'description' => $data['description'] ?? $sound->description,
            'category_id' => $categoryId,
        ];

        if ($file) {
            // Delete old file
            Storage::disk(self::DISK_NAME)->delete($sound->file_path);

            // Store new file
            $originalName = $file->getClientOriginalName();
            $filename = $this->generateUniqueFilename($originalName);
            $filePath = $this->storeFile($file, $filename);

            $updateData = array_merge($updateData, [
                'file_path' => $filePath,
                'file_name' => $originalName,
                'mime_type' => $file->getMimeType() ?? 'audio/mpeg',
                'file_size' => $file->getSize(),
                'duration' => $this->extractAudioDuration($file),
            ]);
        }

        $sound->update($updateData);

        return $sound;
    }

    /**
     * Delete a sound and its file.
     */
    public function deleteSound(Sound $sound): ?bool
    {
        // Delete file from storage
        Storage::disk(self::DISK_NAME)->delete($sound->file_path);

        // Delete record
        return $sound->delete();
    }

    /**
     * Update sort order for sounds in a category.
     *
     * @param  array<int, mixed>  $soundIds
     */
    public function updateSortOrder(array $soundIds): void
    {
        foreach ($soundIds as $index => $soundId) {
            Sound::where('id', $soundId)->update(['sort_order' => $index]);
        }
    }

    /**
     * Generate a unique filename to avoid conflicts.
     */
    private function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $slugName = Str::slug($name);

        return $slugName . '_' . time() . '_' . Str::random(6) . '.' . $extension;
    }

    /**
     * Extract audio duration from file (basic implementation).
     */
    private function extractAudioDuration(UploadedFile $file): ?float
    {
        // This is a basic implementation. In production, you might want to use
        // a more robust solution like getID3 library or FFmpeg

        try {
            $filePath = $file->getRealPath();

            if ($filePath === false) {
                return null;
            }

            return Audio::read($filePath)->getDuration();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get the next sort order for a category.
     */
    private function getNextSortOrder(int $categoryId): int
    {
        $maxOrder = Sound::where('category_id', $categoryId)->max('sort_order');

        return ($maxOrder ?? 0) + 1;
    }

    private function storeFile(UploadedFile $file, string $filename): string|false
    {
        return $file->storePubliclyAs('sounds', $filename, ['disk' => self::DISK_NAME]);
    }
}
