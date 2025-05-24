<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Nature Sounds',
                'description' => 'Sounds from nature including birds, water, wind, and forest ambience.',
            ],
            [
                'name' => 'Musical Instruments',
                'description' => 'Various musical instrument samples and loops.',
            ],
            [
                'name' => 'Voice Effects',
                'description' => 'Voice samples, speech effects, and vocal sounds.',
            ],
            [
                'name' => 'Electronic',
                'description' => 'Electronic music samples, synthesized sounds, and digital effects.',
            ],
            [
                'name' => 'Percussion',
                'description' => 'Drum samples, percussion instruments, and rhythm loops.',
            ],
            [
                'name' => 'Ambient',
                'description' => 'Atmospheric sounds, drones, and ambient textures.',
            ],
            [
                'name' => 'Sound Effects',
                'description' => 'General sound effects for multimedia production.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
