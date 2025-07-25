<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-g">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'VoicePad') }}</title>

    <!-- Additional head content -->
    @stack('head')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200"
      x-data="{
          configOpen: false,
          fadeOutTime: localStorage.getItem('voicePadFadeOutTime') || 0,
          stopOnSecondClick: localStorage.getItem('voicePadStopOnSecondClick') === 'true',
          favoriteSoundIds: JSON.parse(localStorage.getItem('voicePadFavoriteSoundIds') || '[]'),

          updateFadeOutTime(event) {
              this.fadeOutTime = event.target.value;
              localStorage.setItem('voicePadFadeOutTime', this.fadeOutTime);
          },
          updateStopOnSecondClick(event) {
              this.stopOnSecondClick = event.target.checked;
              localStorage.setItem('voicePadStopOnSecondClick', this.stopOnSecondClick);
          },
          isFavorite(soundId) {
              return this.favoriteSoundIds.includes(soundId);
          },
          toggleFavorite(soundId) {
              const index = this.favoriteSoundIds.indexOf(soundId);
              if (index > -1) {
                  this.favoriteSoundIds.splice(index, 1);
              } else {
                  this.favoriteSoundIds.push(soundId);
              }
              localStorage.setItem('voicePadFavoriteSoundIds', JSON.stringify(this.favoriteSoundIds));
              Livewire.dispatch('favoritesUpdated', { soundIds: this.favoriteSoundIds });
          }
      }">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <livewire:header />

        <!-- Page Content -->
        <main class="flex-grow container mx-auto px-4 py-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        @include('partials.footer')

        <!-- Config Modal -->
        <div x-show="configOpen" @click.away="configOpen = false" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 p-4" style="display: none;">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl border-gray-700 border w-full max-w-md" @click.stop>
                <h2 class="text-xl font-semibold mb-4">Configuration</h2>
                <div class="mb-4">
                    <label for="fadeOutTime" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fadeout Time (ms)</label>
                    <input type="number" id="fadeOutTime" name="fadeOutTime" :value="fadeOutTime" @input="updateFadeOutTime"
                           class="mt-1 px-2 py-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" :checked="stopOnSecondClick" @change="updateStopOnSecondClick"
                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Stop playing on second click</span>
                    </label>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="configOpen = false" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Close</button>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    <script>
        // Global audio object and currently playing sound ID
        let currentAudio = null;
        let currentSoundId = null; // Tracks the ID of the sound object in currentAudio
        let fadeOutTimer = null;

        window.playSound = function(soundUrl, soundId) {
            const fadeOutTime = parseInt(localStorage.getItem('voicePadFadeOutTime') || '0');
            const stopOnSecondClick = localStorage.getItem('voicePadStopOnSecondClick') === 'true';

            if (currentAudio) {
                const previouslyPlayingSoundId = currentSoundId; // Store before potentially changing

                if (currentSoundId === soundId && stopOnSecondClick) {
                    // Stop the current sound
                    if (fadeOutTime > 0) {
                        let volume = currentAudio.volume;
                        if(fadeOutTimer) clearInterval(fadeOutTimer);
                        fadeOutTimer = setInterval(() => {
                            volume -= 0.1; // Decrease volume
                            if (volume <= 0) {
                                currentAudio.pause();
                                currentAudio.currentTime = 0;
                                clearInterval(fadeOutTimer);
                                currentAudio = null;
                                currentSoundId = null;
                                localStorage.removeItem('voicePadCurrentPlayingSoundId');
                                window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null } }));
                                Livewire.dispatch('sound-stopped', { id: soundId });
                            } else {
                                currentAudio.volume = volume;
                            }
                        }, fadeOutTime / 10);
                    } else {
                        currentAudio.pause();
                        currentAudio.currentTime = 0;
                        currentAudio = null;
                        currentSoundId = null;
                        localStorage.removeItem('voicePadCurrentPlayingSoundId');
                        window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null } }));
                        Livewire.dispatch('sound-stopped', { id: soundId });
                    }
                    return; // Sound stopped
                } else {
                    // Stop previous sound immediately before playing a new one
                    currentAudio.pause();
                    currentAudio.currentTime = 0;
                    if(fadeOutTimer) clearInterval(fadeOutTimer);
                    // Don't remove voicePadCurrentPlayingSoundId yet, it will be overwritten by the new sound
                    // Dispatch that the *previous* sound stopped
                    window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null, oldSoundId: previouslyPlayingSoundId } })); // Indicate old one stopped
                    Livewire.dispatch('sound-stopped', { id: previouslyPlayingSoundId });
                }
            }

            // Play new sound
            currentAudio = new Audio(soundUrl);
            currentAudio.volume = 1;
            currentAudio.play().then(() => {
                currentSoundId = soundId;
                localStorage.setItem('voicePadCurrentPlayingSoundId', String(soundId));
                window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: soundId } }));
                Livewire.dispatch('sound-started', { id: soundId });
            }).catch(error => {
                console.error("Error playing sound:", error);
                // Ensure state is clean if play fails
                if(currentSoundId === soundId) { // If it was set optimistically
                    localStorage.removeItem('voicePadCurrentPlayingSoundId');
                    window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null } }));
                    currentSoundId = null;
                }
            });

            currentAudio.onended = () => {
                if(fadeOutTimer) clearInterval(fadeOutTimer);
                localStorage.removeItem('voicePadCurrentPlayingSoundId');
                window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null } }));
                Livewire.dispatch('sound-stopped', { id: soundId }); // Use the soundId that ended
                currentAudio = null;
                currentSoundId = null;
            };
        }

        window.stopSound = function() { // Assumes this is a global stop for any playing sound
            if (currentAudio) {
                const soundIdToStop = currentSoundId;
                const fadeOutTime = parseInt(localStorage.getItem('voicePadFadeOutTime') || '0');
                if (fadeOutTime > 0) {
                    let volume = currentAudio.volume;
                    if(fadeOutTimer) clearInterval(fadeOutTimer);
                    fadeOutTimer = setInterval(() => {
                        volume -= 0.1;
                        if (volume <= 0) {
                            currentAudio.pause();
                            currentAudio.currentTime = 0;
                            clearInterval(fadeOutTimer);
                            localStorage.removeItem('voicePadCurrentPlayingSoundId');
                            window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null } }));
                            if (soundIdToStop !== null) Livewire.dispatch('sound-stopped', { id: soundIdToStop });
                            currentAudio = null;
                            currentSoundId = null;
                        } else {
                            currentAudio.volume = volume;
                        }
                    }, fadeOutTime / 10);
                } else {
                    currentAudio.pause();
                    currentAudio.currentTime = 0;
                    localStorage.removeItem('voicePadCurrentPlayingSoundId');
                    window.dispatchEvent(new CustomEvent('now-playing-updated', { detail: { soundId: null } }));
                    if (soundIdToStop !== null) Livewire.dispatch('sound-stopped', { id: soundIdToStop });
                    currentAudio = null;
                    currentSoundId = null;
                }
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('openConfigModal', (event) => {});

            Livewire.on('playSound', (event) => {
                if (event.soundUrl && typeof event.soundId !== 'undefined') {
                    window.playSound(event.soundUrl, event.soundId);
                }
            });

            // Listener for when favorites are updated in local storage by Alpine
            // This is already handled by Livewire.dispatch('favoritesUpdated') in toggleFavorite
        });
    </script>
</body>
</html>
