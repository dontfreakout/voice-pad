<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\SoundResource;
use App\Models\Category;
use App\Models\Sound;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Sounds')]
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
     * Display a listing of the sounds for given IDs.
     *
     * @queryParam ids array required The array of sound IDs. Example: [1, 2, 3]
     */
    public function indexByIds(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:sounds,id',
        ]);

        $ids = $request->query('ids');

        $sounds = Sound::with('category')->whereIn('id', $ids)->get();

        return SoundResource::collection($sounds);
    }
}
