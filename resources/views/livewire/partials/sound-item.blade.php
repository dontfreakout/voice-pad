@props([
    'sound',
    'showCategoryLink' => true,
    'playingSoundId' => null
])

<div wire:key="sound-{{ $sound->id }}"
     x-data="{
         localIsFavorite: isFavorite({{ $sound->id }}),
         localIsPlaying: String({{ $playingSoundId ?? 'null' }}) === String({{ $sound->id }})
     }"
     @favorites-updated.window="localIsFavorite = isFavorite({{ $sound->id }})"
     @now-playing-updated.window="localIsPlaying = String($event.detail.soundId) === String({{ $sound->id }})"
     :class="{
         'sound-item': true,
         'bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200 flex flex-col justify-between': displayMode === 'grid',
         'flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-md shadow-sm': displayMode === 'list'
     }">

    <div
        @click.prevent="window.playSound('{{ $sound->file_url ?? Storage::url($sound->file_path) }}', {{ $sound->id }})"
        title="Play {{ $sound->name }}"
        :class="{'flex-grow': true, 'flex items-center space-x-3': displayMode === 'list'}"
        class="cursor-pointer"
    >
        <button
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
        </div>
    </div>

    <div :class="{'flex items-center': true, 'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 justify-between': displayMode === 'grid', 'flex-shrink-0 flex-row-reverse': displayMode === 'list'}">
        <div class="flex space-x-2">
            <button
                    type="button"
                    @click.prevent="toggleFavorite({{ $sound->id }}); localIsFavorite = isFavorite({{ $sound->id }})"
                    title="Toggle Favorite"
                    class="p-2 rounded-md hover:text-red-500 dark:hover:text-red-400 focus:outline-none transition-colors duration-150 ease-in-out cursor-pointer"
                    :class="localIsFavorite ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500'">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                </svg>
                <span class="sr-only">Toggle Favorite</span>
            </button>

            <a href="{{ route('sound.show', $sound) }}"
               title="View Sound Details"
               class="p-2 rounded-md text-indigo-500 dark:text-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-300 focus:outline-none transition-colors duration-150 ease-in-out cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                </svg>
                <span class="sr-only">Share</span>
            </a>
        </div>

        @if($showCategoryLink && $sound->category)
            <a href="{{ route('category.show', $sound->category) }}"
               :class="['text-xs text-indigo-500 dark:text-indigo-400 hover:underline', displayMode === 'grid' ? 'block mt-1' : 'ml-2']">
                {{ $sound->category->name }}
            </a>
        @endif
    </div>

    <!-- Schema.org markup for rich snippets -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "AudioObject",
        "name": "{{ $sound->name }}",
        "contentUrl": "{{ $sound->file_url ?? Storage::url($sound->file_path) }}",
        "duration": "PT{{ (int)floor($sound->duration / 60) }}M{{ (int)($sound->duration % 60) }}S",
        "encodingFormat": "{{ $sound->mime_type }}",
        @if($sound->description)
        "description": "{{ $sound->description }}",
        @endif
        "uploadDate": "{{ $sound->created_at->toIso8601String() }}",
        "url": "{{ route('sound.show', $sound) }}"
    }
    </script>
</div>






