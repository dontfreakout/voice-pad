@props([
    'sound',
    'displayMode' => 'list', // This prop will now be dynamically bound from Alpine in parent components
    'showCategoryLink' => true,
    'playingSoundId' => null // Pass playingSoundId to correctly initialize localIsPlaying
])

<div wire:key="sound-{{ $sound->id }}"
     x-data="{
         localIsFavorite: isFavorite({{ $sound->id }}),
         // Ensure localIsPlaying is correctly initialized based on playingSoundId prop
         localIsPlaying: String({{ $playingSoundId ?? 'null' }}) === String({{ $sound->id }})
     }"
     @favorites-updated.window="localIsFavorite = isFavorite({{ $sound->id }})"
     @now-playing-updated.window="localIsPlaying = String($event.detail.soundId) === String({{ $sound->id }})"
     :class="{
         'sound-item': true,
         'bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200 flex flex-col justify-between': displayMode === 'grid',
         'flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-md shadow-sm': displayMode === 'list'
     }">

    <div :class="{'flex-grow': true, 'flex items-center space-x-3': displayMode === 'list'}">
        <button
            @click.prevent="window.playSound('{{ $sound->file_url ?? Storage::url($sound->file_path) }}', {{ $sound->id }})"
            title="Play {{ $sound->name }}"
            :class="[
                displayMode === 'grid' ? 'w-full mb-2' : '',
                'flex items-center justify-center p-2 rounded-md cursor-pointer transition-colors duration-150 ease-in-out',
                localIsPlaying ? 'bg-green-500 text-white' : 'bg-indigo-500 hover:bg-indigo-600 text-white'
            ]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <g x-show="localIsPlaying">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10v4M15 10v4" />
                    </g>
                    <g x-show="!localIsPlaying">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </g>
            </svg>
            <span class="sr-only" x-text="localIsPlaying ? 'Pause' : 'Play'"></span>
        </button>

        <div :class="{'text-center': displayMode === 'grid'}">
            <h3 :class="['font-semibold text-gray-800 dark:text-white', displayMode === 'grid' ? 'text-md mb-1' : 'text-lg']">{{ $sound->name }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Duration: {{ $sound->duration ? gmdate('i:s', $sound->duration) : 'N/A' }}
            </p>
            @if($showCategoryLink && $sound->category)
                <a href="{{ route('category.show', $sound->category) }}"
                   :class="['text-xs text-indigo-500 dark:text-indigo-400 hover:underline', displayMode === 'grid' ? 'block mt-1' : 'ml-2']">
                    {{ $sound->category->name }}
                </a>
            @endif
        </div>
    </div>

    <div :class="{'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700': displayMode === 'grid', 'flex-shrink-0': displayMode === 'list'}">
        <button
                type="button"
                @click.prevent="toggleFavorite({{ $sound->id }}); localIsFavorite = isFavorite({{ $sound->id }})"
                title="Toggle Favorite"
                class="p-2 rounded-md hover:text-red-500 dark:hover:text-red-400 focus:outline-none transition-colors duration-150 ease-in-out"
                :class="localIsFavorite ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
            </svg>
            <span class="sr-only">Toggle Favorite</span>
        </button>
    </div>
</div>






