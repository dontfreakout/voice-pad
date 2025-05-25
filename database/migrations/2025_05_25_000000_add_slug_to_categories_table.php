<?php

declare(strict_types=1);

use App\Models\Category; // Added import for Category model
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str; // Added import for Str

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            // Add slug column, make it unique and initially nullable
            $table->string('slug')->unique()->after('description')->nullable();
        });

        // Populate slug for existing categories
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'slug')) {
            Category::query()
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->cursor()
                ->each(function (Category $category): void {
                    $category->slug = Str::slug($category->name);
                    // Ensure uniqueness if multiple categories have the same name before slugification
                    $baseSlug = $category->slug;
                    $count = 1;
                    while (Category::where('slug', $category->slug)->where('id', '!=', $category->id)->exists()) {
                        $count++;
                        $category->slug = $baseSlug . '-' . $count;
                    }
                    $category->saveQuietly(); // Use saveQuietly to avoid triggering model events
                });
        }

        // Make slug non-nullable after populating existing ones
        // The unique constraint is already added in the first Schema::table call.
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('slug');
        });
    }
};
