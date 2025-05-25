<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\Sound;
use Livewire\Component;

class CategoryShow extends Component
{
    public Category $category;

    /** @var array<string, mixed> */
    protected $listeners = [
        'sound-started' => 'updatePlayingSoundId',
        'sound-stopped' => 'clearPlayingSoundId',
    ];

    public function mount(Category $category): void
    {
        $this->category = $category;
    }

    public function render(): mixed
    {
        $sounds = Sound::where('category_id', $this->category->id)->get();

        return view('livewire.category-show', [
            'sounds' => $sounds,
        ])->layout('layouts.app');
    }
}
