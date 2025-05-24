<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\Sound;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryShow extends Component
{
    use WithPagination;

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
        $sounds = Sound::where('category_id', $this->category->id)
            ->paginate(10);

        return view('livewire.category-show', [
            'sounds' => $sounds,
        ])->layout('layouts.app');
    }
}
