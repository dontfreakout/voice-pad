<div class="space-y-8" x-data="{ displayMode: localStorage.getItem('displayMode') || 'list' }"
     x-init="
        const favoriteIds = JSON.parse(localStorage.getItem('voicePadFavoriteSoundIds') || '[]');
        if (favoriteIds && favoriteIds.length > 0) {
            $dispatch('favoritesUpdated', {'soundIds': favoriteIds});
        }
        $watch('displayMode', value => localStorage.setItem('displayMode', value));
     "
     @display-mode-changed.window="displayMode = $event.detail.mode">
    <!-- Favorite Sounds -->
    <section>
        @if(count($favoriteSounds) > 0)
            <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Favorite Sounds</h2>
            <div :class="{ 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6  gap-4': displayMode === 'grid', 'space-y-2': displayMode === 'list' }">
                @foreach($favoriteSounds as $sound)
                    @include('livewire.partials.sound-item', ['sound' => $sound, 'displayMode' => '{{displayMode}}', 'playingSoundId' => $playingSoundId])
                @endforeach
            </div>
        @endif
    </section>

    <!-- Categories -->
    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Categories</h2>
        @if(count($categories) > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('category.show', $category) }}"
                       class="block p-6 bg-white dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:drop-shadow-violet-600 rounded-lg shadow hover:shadow-lg transition-all duration-200 ease-in-out">
                        <h3 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $category->sounds_count }} sound{{ $category->sounds_count !== 1 ? 's' : '' }}</p>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">No categories available.</p>
        @endif
    </section>
</div>

