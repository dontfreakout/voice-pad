<?php

declare(strict_types=1);

use App\Models\Sound;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sounds', function (Blueprint $table): void {
            // Add slug column, make it unique and initially nullable
            $table->string('slug')->unique()->after('name')->nullable();
        });

        // Populate slug for existing sounds
        if (Schema::hasTable('sounds') && Schema::hasColumn('sounds', 'slug')) {
            Sound::query()
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->cursor()
                ->each(function (Sound $sound): void {
                    $sound->slug = Str::slug($sound->name);
                    // Ensure uniqueness if multiple sounds have the same name before slugification
                    $baseSlug = $sound->slug;
                    $count = 1;
                    while (Sound::where('slug', $sound->slug)->where('id', '!=', $sound->id)->exists()) {
                        $count++;
                        $sound->slug = $baseSlug . '-' . $count;
                    }
                    $sound->saveQuietly(); // Use saveQuietly to avoid triggering model events
                });
        }

        // Make slug non-nullable after populating existing ones
        Schema::table('sounds', function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sounds', function (Blueprint $table): void {
            $table->dropColumn('slug');
        });
    }
};