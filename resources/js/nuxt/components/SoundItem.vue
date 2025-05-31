<template>
  <div :class="itemClasses">
    <div @click.prevent="handlePlaybackToggle" title="Play/Pause Sound" :class="clickableAreaClasses" class="cursor-pointer">
      <button :class="playButtonClasses">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <g v-if="isThisSoundPlaying">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10v4M15 10v4" />
          </g>
          <g v-else>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </g>
        </svg>
        <span class="sr-only">{{ isThisSoundPlaying ? 'Pause' : 'Play' }}</span>
      </button>

      <div :class="{'text-center': currentDisplayMode === 'grid'}">
        <h3 :class="['font-semibold text-gray-800 dark:text-white', currentDisplayMode === 'grid' ? 'text-md mb-1' : 'text-lg']">{{ sound.name }}</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Duration: {{ formattedDuration }}
        </p>
      </div>
    </div>

    <div :class="footerClasses">
      <button
        type="button"
        @click.prevent="handleFavoriteToggle"
        title="Toggle Favorite"
        class="p-2 rounded-md hover:text-red-500 dark:hover:text-red-400 focus:outline-none transition-colors duration-150 ease-in-out cursor-pointer"
        :class="isThisSoundFavorite ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
        </svg>
        <span class="sr-only">Toggle Favorite</span>
      </button>
      <NuxtLink v-if="props.showCategoryLink && sound.category"
             :to="`/categories/${sound.category.id}`"
             :class="['text-xs text-indigo-500 dark:text-indigo-400 hover:underline', currentDisplayMode === 'grid' ? 'block mt-1' : 'ml-2']">
        {{ sound.category.name }}
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useUiStore } from '~/stores/ui';
import { useFavoritesStore } from '~/stores/favorites';
import type { Sound } from '~/types';

const props = defineProps({
  sound: {
    type: Object as () => Sound,
    required: true,
  },
  showCategoryLink: { // This prop can remain as it's purely presentational
    type: Boolean,
    default: true,
  }
  // Removed playingSoundId, isFavorite, displayMode props as they come from stores now
});

const uiStore = useUiStore();
const favoritesStore = useFavoritesStore();

// This specific audio instance for this sound item.
// It's created on demand when play is requested for this item.
const localAudioInstance = ref(null);
const duration = ref(props.sound.duration_seconds || 0);

const currentDisplayMode = computed(() => uiStore.getDisplayMode);
const isThisSoundPlaying = computed(() => uiStore.isPlaying(props.sound.id));
const isThisSoundFavorite = computed(() => favoritesStore.isFavorite(props.sound.id));

const formattedDuration = computed(() => {
  const totalSeconds = Number(duration.value);
  if (isNaN(totalSeconds) || totalSeconds === 0) return 'N/A'; // Or props.sound.duration_formatted if available
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
  return `${minutes}:${seconds}`;
});

const createAndSetupAudioInstance = () => {
  if (!props.sound.file_url) return null;
  const audio = new Audio(props.sound.file_url);
  audio.addEventListener('loadedmetadata', () => {
    if (audio) duration.value = audio.duration;
  });
  audio.addEventListener('ended', () => {
    uiStore.handleSoundEnded(props.sound.id);
  });
  audio.preload = 'metadata'; // Preload metadata for duration
  return audio;
};

function handlePlaybackToggle() {
  if (isThisSoundPlaying.value) { // If this sound is currently playing (according to store), then pause it
    uiStore.pauseCurrentSound();
  } else { // If this sound is not playing or another sound is playing
    if (!localAudioInstance.value || localAudioInstance.value.src !== props.sound.file_url) {
      // If instance doesn't exist, or is for a different sound (e.g. prop changed), create new one
      if (localAudioInstance.value) { // Clean up old instance if any
          localAudioInstance.value.removeEventListener('ended', () => uiStore.handleSoundEnded(props.sound.id));
      }
      localAudioInstance.value = createAndSetupAudioInstance();
    }
    if (localAudioInstance.value) {
      uiStore.setPlayingSound(props.sound.id, localAudioInstance.value);
    }
  }
}

function handleFavoriteToggle() {
  favoritesStore.toggleFavorite(props.sound.id);
  // If the sound was just added to favorites, and we want to update the detailed favoriteSounds list in the store:
  if (favoritesStore.isFavorite(props.sound.id)) {
      // Option 1: Fetch all favorites again (might be too heavy)
      // favoritesStore.fetchFavoriteSounds();
      // Option 2: Add this specific sound object to the store's list if not already there by full object
      // This assumes `props.sound` is a complete Sound object.
      favoritesStore.addSoundToFavoritesList(props.sound);
  }
}

onMounted(() => {
  // If sound duration is not initially provided, try to load it if file_url exists
  // This helps if the parent component doesn't pass duration_seconds
  if (!duration.value && props.sound.file_url) {
    const audioForDuration = new Audio(props.sound.file_url);
    audioForDuration.addEventListener('loadedmetadata', () => {
      duration.value = audioForDuration.duration;
    });
    // No need to keep this audioForDuration instance beyond getting metadata.
  }
});

onUnmounted(() => {
  // If this sound item's audio instance is the one currently playing in the store,
  // and the component is unmounted, we should stop it to prevent leaks.
  if (uiStore.currentlyPlayingSoundId === props.sound.id && localAudioInstance.value === uiStore.currentAudioInstance) {
    uiStore.stopCurrentSound();
  }
  // Clean up local audio instance if it exists
  if (localAudioInstance.value) {
    localAudioInstance.value.removeEventListener('ended', () => uiStore.handleSoundEnded(props.sound.id));
    localAudioInstance.value.pause(); // Ensure it's paused
    localAudioInstance.value = null;
  }
});

// Watcher to ensure localAudioInstance is managed if the sound source changes (e.g. list virtualization)
watch(() => props.sound.id, () => {
    if (localAudioInstance.value) {
        if (uiStore.currentlyPlayingSoundId === props.sound.id && localAudioInstance.value === uiStore.currentAudioInstance) {
            uiStore.stopCurrentSound();
        }
        localAudioInstance.value.removeEventListener('ended', () => uiStore.handleSoundEnded(props.sound.id));
        localAudioInstance.value.pause();
        localAudioInstance.value = null; // Reset for the new sound prop
    }
    duration.value = props.sound.duration_seconds || 0; // Reset duration
});


// Dynamic classes based on display mode from store
const itemClasses = computed(() => ({
  'sound-item': true,
  'bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200 flex flex-col justify-between': currentDisplayMode.value === 'grid',
  'flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-md shadow-sm': currentDisplayMode.value === 'list'
}));

const clickableAreaClasses = computed(() => ({
  'flex-grow': true,
  'flex items-center space-x-3': currentDisplayMode.value === 'list',
  'flex flex-col items-center w-full': currentDisplayMode.value === 'grid',
}));

const playButtonClasses = computed(() => [
  currentDisplayMode.value === 'grid' ? 'w-full mb-2' : '',
  'flex items-center justify-center p-2 rounded-md cursor-pointer transition-colors duration-150 ease-in-out',
  isThisSoundPlaying.value ? 'bg-green-500 text-white' : 'bg-indigo-500 hover:bg-indigo-600 text-white'
]);

const footerClasses = computed(() => ({
  'flex items-center': true,
  'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 justify-between w-full': currentDisplayMode.value === 'grid',
  'flex-shrink-0 flex-row-reverse': currentDisplayMode.value === 'list'
}));

</script>

<style scoped>
/* Minimal specific styles, relying on Tailwind utility classes */
</style>
