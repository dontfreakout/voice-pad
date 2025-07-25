<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Sound;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoundDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_sound_detail_page_accessible_by_slug(): void
    {
        // Create a category
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'A test category',
        ]);

        // Create a sound
        $sound = Sound::create([
            'name' => 'Test Sound',
            'description' => 'A test sound',
            'file_path' => 'test/sound.mp3',
            'file_name' => 'sound.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 1024,
            'duration' => 30.0,
            'category_id' => $category->id,
            'sort_order' => 1,
        ]);

        // Assert slug was generated
        $this->assertNotNull($sound->slug);
        $this->assertEquals('test-sound', $sound->slug);

        // Test that the sound detail page is accessible by slug
        $response = $this->get("/sounds/{$sound->slug}");
        $response->assertStatus(200);
        $response->assertSee($sound->name);
        $response->assertSee($sound->description);

        // Test that Open Graph meta tags are present
        $response->assertSee('<meta property="og:title" content="' . $sound->name . '">', false);
        $response->assertSee('<meta property="og:type" content="music.song">', false);
        $response->assertSee('<meta property="og:audio" content="', false);
        $response->assertSee('<meta property="og:description" content="' . $sound->description . '">', false);

        // Test that Schema.org markup is present
        $response->assertSee('"@type": "MusicRecording"', false);
        $response->assertSee('"name": "' . $sound->name . '"', false);
    }

    public function test_sound_slug_generation(): void
    {
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'A test category',
        ]);

        // Test basic slug generation
        $sound1 = Sound::create([
            'name' => 'Hello World',
            'file_path' => 'test/sound1.mp3',
            'file_name' => 'sound1.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 1024,
            'category_id' => $category->id,
            'sort_order' => 1,
        ]);

        $this->assertEquals('hello-world', $sound1->slug);

        // Test unique slug generation for duplicate names
        $sound2 = Sound::create([
            'name' => 'Hello World',
            'file_path' => 'test/sound2.mp3',
            'file_name' => 'sound2.mp3',
            'mime_type' => 'audio/mpeg',
            'file_size' => 1024,
            'category_id' => $category->id,
            'sort_order' => 2,
        ]);

        $this->assertEquals('hello-world-2', $sound2->slug);
    }
}