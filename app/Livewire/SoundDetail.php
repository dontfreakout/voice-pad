<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Sound;
use Livewire\Component;

class SoundDetail extends Component
{
    public Sound $sound;
    public ?int $playingSoundId = null;

    public function mount(Sound $sound): void
    {
        $this->sound = $sound;
    }

    public function updatePlayingSoundId(int $id): void
    {
        $this->playingSoundId = $id;
    }

    public function clearPlayingSoundId(): void
    {
        $this->playingSoundId = null;
    }

    public function render()
    {
        // Set page title
        $title = $this->sound->name . ' - ' . config('app.name', 'VoicePad');
        
        return view('livewire.sound-detail', [
            'pageTitle' => $title,
        ])
            ->layout('layouts.app', [
                'title' => $title,
            ]);
    }
}
