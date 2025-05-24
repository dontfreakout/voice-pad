<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\Sound;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Home extends Component
{
    /** @var Collection<int, Sound> */
    public Collection $favoriteSounds;

    /** @var Collection<int, Category> */
    public Collection $categories;

    public ?int $playingSoundId = null;

    /** @var int[] */
    public array $favoriteSoundIds = [];

    /** @var array<string, mixed> */
    protected $listeners = [
        'sound-started' => 'updatePlayingSoundId',
        'sound-stopped' => 'clearPlayingSoundId',
        'favoritesUpdated' => 'updateFavoriteSounds', // Listen for updates from Alpine
    ];

    public function __construct()
    {
        $this->favoriteSounds = collect();
        $this->categories = collect();
    }

    public function mount(): void
    {
        $this->loadCategories();
    }

    /**
     * @param  int[]  $soundIds
     */
    #[On('favoritesUpdated')]
    public function updateFavoriteSounds(array $soundIds): void
    {
        $this->favoriteSoundIds = $soundIds;
        $this->loadFavoriteSounds();
    }

    public function loadFavoriteSounds(): void
    {
        if (! empty($this->favoriteSoundIds)) {
            $this->favoriteSounds = Sound::with('category')
                ->whereIn('id', $this->favoriteSoundIds)
                ->get();
        } else {
            $this->favoriteSounds = collect(); // Empty collection
        }
    }

    public function loadCategories(): void
    {
        $this->categories = Category::withCount('sounds')->get();
    }

    public function render(): mixed
    {
        return view('livewire.home')
            ->layout('layouts.app');
    }
}
