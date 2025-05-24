<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SoundResource;
use App\Models\Category;
use App\Models\Sound;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SoundController extends Controller
{
    /**
     * Display a listing of the sounds for a given category.
     */
    public function indexByCategory(Category $category): AnonymousResourceCollection
    {
        // Ensure the category relationship is loaded if you plan to use it in the SoundResource
        // For example, if SoundResource includes category information.
        // $sounds = $category->sounds()->with('category')->get(); // Eager load category if needed

        $sounds = $category->sounds;

        return SoundResource::collection($sounds);
    }

    /**
     * Display a listing of all sounds. (Optional, based on requirements)
     */
    public function indexAllSounds(): AnonymousResourceCollection
    {
        return SoundResource::collection(Sound::with('category')->get());
    }
}
