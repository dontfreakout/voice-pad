<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\Sound;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    /** @var Collection<int, Sound> */
    public Collection $favoriteSounds;

    public ?int $playingSoundId = null;

    /** @var int[] */
    public array $favoriteSoundIds = [];

    /** @var array<string, mixed> */
    protected $listeners = [
        'sound-started' => 'updatePlayingSoundId',
        'sound-stopped' => 'clearPlayingSoundId',
        'favoritesUpdated' => 'updateFavoriteSounds',
    ];

    public function __construct()
    {
        $this->favoriteSounds = new Collection();
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
            $this->favoriteSounds = new Collection();
        }
    }

    public function render(): mixed
    {
        return view('livewire.home', [
            'categories' => Category::withCount('sounds')->paginate(18),
        ])
            ->layout('layouts.app');
    }
}
