<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;

class Header extends Component
{
    public function mount(): void
    {
        // Display mode is now handled by Alpine.js and localStorage
    }

    public function openConfigModal(): void
    {
        $this->dispatch('openConfigModal'); // Dispatch to Alpine.js in app.blade.php
    }

    public function render(): mixed
    {
        return view('livewire.header');
    }
}
