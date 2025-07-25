@push('head')
    <!-- Open Graph Protocol meta tags for sound/music -->
    <meta property="og:title" content="{{ $sound->name }}">
    <meta property="og:type" content="music.song">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:audio" content="{{ $sound->file_url }}">
    <meta property="og:audio:type" content="{{ $sound->mime_type }}">
    @if($sound->description)
    <meta property="og:description" content="{{ $sound->description }}">
    @endif
    @if($sound->category)
    <meta property="music:genre" content="{{ $sound->category->name }}">
    @endif
    @if($sound->duration)
    <meta property="music:duration" content="{{ (int)$sound->duration }}">
    @endif
    <meta property="og:site_name" content="{{ config('app.name', 'VoicePad') }}">
    
    <!-- Twitter Card meta tags -->
    <meta name="twitter:card" content="player">
    <meta name="twitter:title" content="{{ $sound->name }}">
    @if($sound->description)
    <meta name="twitter:description" content="{{ $sound->description }}">
    @endif
    <meta name="twitter:player" content="{{ $sound->file_url }}">
    <meta name="twitter:player:width" content="320">
    <meta name="twitter:player:height" content="240">
@endpush

<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $sound->name }}</h1>
                @if($sound->category)
                    <a href="{{ route('category.show', $sound->category) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                        {{ $sound->category->name }}
                    </a>
                @endif
            </div>

            <div class="mt-4 md:mt-0 flex items-center space-x-4">
                <span class="text-gray-600 dark:text-gray-400">
                    Duration: {{ $sound->formatted_duration }}
                </span>
                <span class="text-gray-600 dark:text-gray-400">
                    Size: {{ $sound->formatted_file_size }}
                </span>
            </div>
        </div>

        <div class="mb-8">
            @if($sound->description)
                <p class="text-gray-700 dark:text-gray-300">{{ $sound->description }}</p>
            @endif
        </div>

        <div class="flex justify-center mb-8">
            <div
                x-data="{
                    isPlaying: {{ $playingSoundId === $sound->id ? 'true' : 'false' }},
                    togglePlay() {
                        if (this.isPlaying) {
                            window.stopSound();
                        } else {
                            window.playSound('{{ $sound->file_url }}', {{ $sound->id }});
                        }
                        this.isPlaying = !this.isPlaying;
                    }
                }"
                @now-playing-updated.window="isPlaying = $event.detail.soundId === {{ $sound->id }}"
                class="flex flex-col items-center"
            >
                <button
                    @click="togglePlay"
                    class="w-24 h-24 rounded-full flex items-center justify-center transition-colors duration-200"
                    :class="isPlaying ? 'bg-green-500 hover:bg-green-600' : 'bg-indigo-500 hover:bg-indigo-600'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <g x-show="isPlaying">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10v4M15 10v4" />
                        </g>
                        <g x-show="!isPlaying">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </g>
                    </svg>
                </button>
                <span class="mt-2 text-gray-700 dark:text-gray-300" x-text="isPlaying ? 'Pause' : 'Play'"></span>
            </div>
        </div>

        <div class="flex justify-center">
            <a href="{{ route('home') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                &larr; Back to Home
            </a>
        </div>
    </div>

    <!-- Schema.org markup for rich snippets -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "MusicRecording",
        "name": "{{ $sound->name }}",
        "duration": "PT{{ (int)floor($sound->duration / 60) }}M{{ (int)($sound->duration % 60) }}S",
        "url": "{{ route('sound.show', $sound) }}",
        @if($sound->description)
        "description": "{{ $sound->description }}",
        @endif
        @if($sound->category)
        "genre": "{{ $sound->category->name }}",
        @endif
        "uploadDate": "{{ $sound->created_at->toIso8601String() }}",
        "audio": {
            "@type": "AudioObject",
            "contentUrl": "{{ $sound->file_url }}",
            "encodingFormat": "{{ $sound->mime_type }}",
            "duration": "PT{{ (int)floor($sound->duration / 60) }}M{{ (int)($sound->duration % 60) }}S",
            "name": "{{ $sound->name }}"
        }
    }
    </script>
</div>
