<div class="space-y-6" x-data="{
        displayMode: localStorage.getItem('displayMode') || 'list',
        soundsToPreload: {{ json_encode($sounds->pluck('file_url', 'id')->all()) }},
        preloadAudio() {
            Object.values(this.soundsToPreload).forEach(url => {
                if (url) {
                    const audio = new Audio();
                    audio.src = url;
                }
            });
        }
    }"
    x-init="preloadAudio()"
    @display-mode-changed.window="displayMode = $event.detail.mode">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ $category->name }}</h1>
        <a href="{{ route('home') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to Home</a>
    </div>

    @if($sounds->count() > 0)
        <div :class="{ 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4': displayMode === 'grid', 'space-y-2': displayMode === 'list' }">
            @foreach($sounds as $sound)
                @include('livewire.partials.sound-item', ['sound' => $sound, 'displayMode' => '{{displayMode}}', 'showCategoryLink' => false])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $sounds->links() }}
        </div>
    @else
        <p class="text-gray-600 dark:text-gray-400">No sounds found in this category yet.</p>
    @endif
</div>

