<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Categories')]
class CategoryController extends Controller
{
    /**
     * Display a listing of all categories.
     */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::withCount('sounds')->get());
    }
}
