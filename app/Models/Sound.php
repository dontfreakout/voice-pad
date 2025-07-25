<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\SoundService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $file_path
 * @property string $file_name
 * @property string $mime_type
 * @property int $file_size
 * @property float|null $duration
 * @property int $category_id
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category $category
 * @property-read string $file_url
 * @property-read string $formatted_duration
 * @property-read string $formatted_file_size
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sound whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Sound extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'duration',
        'category_id',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration' => 'float',
        'file_size' => 'integer',
        'sort_order' => 'integer',
        'category_id' => 'integer',
    ];

    /**
     * Get the category that owns the sound.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the URL to the sound file.
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::disk(SoundService::DISK_NAME)->url($this->file_path);
    }

    /**
     * Get formatted duration in MM:SS format.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (! $this->duration) {
            return '00:00';
        }

        $minutes = (int) floor($this->duration / 60);
        $seconds = (int) ($this->duration % 60);

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = (float) $this->file_size;
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return round($bytes, 2) . ' ' . $units[$unit];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Sound $sound): void {
            if (empty($sound->slug)) {
                $sound->slug = Str::slug($sound->name);
            }
        });

        static::updating(function (Sound $sound): void {
            if (empty($sound->slug) || ($sound->isDirty('name') && ! $sound->isDirty('slug'))) {
                $sound->slug = Str::slug($sound->name);
            }
        });
    }
}
