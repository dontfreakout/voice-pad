<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Sound;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SoundStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSounds = Sound::count();
        $totalCategories = Category::count();
        $totalDuration = (float) Sound::sum('duration');
        $totalFileSize = (int) Sound::sum('file_size');

        // Format total duration to hours:minutes:seconds
        $hours = (int) floor($totalDuration / 3600);
        $minutes = (int) floor(($totalDuration % 3600) / 60);
        $seconds = (int) ($totalDuration % 60);
        $formattedDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // Format total file size
        $formattedSize = $this->formatBytes($totalFileSize);

        return [
            Stat::make('Total Sounds', (string) $totalSounds)
                ->description('Audio files uploaded')
                ->descriptionIcon('heroicon-m-musical-note')
                ->color('success'),

            Stat::make('Categories', (string) $totalCategories)
                ->description('Sound categories')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),

            Stat::make('Total Duration', $formattedDuration)
                ->description('Combined audio length')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Storage', $formattedSize)
                ->description('Disk space used')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
        ];
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytesFloat = (float) $bytes;
        $unit = 0;

        while ($bytesFloat >= 1024 && $unit < count($units) - 1) {
            $bytesFloat /= 1024;
            $unit++;
        }

        return round($bytesFloat, 2) . ' ' . $units[$unit];
    }
}
