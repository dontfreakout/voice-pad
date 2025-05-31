<template>
  <div :class="itemClasses">
    <div @click.prevent="togglePlayback" title="Play/Pause" :class="clickableAreaClasses" class="cursor-pointer">
      <button :class="playButtonClasses">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <g v-if="isCurrentlyPlaying">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10v4M15 10v4" />
          </g>
          <g v-else>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </g>
        </svg>
        <span class="sr-only">{{ isCurrentlyPlaying ? 'Pause' : 'Play' }}</span>
      </button>

      <div :class="{'text-center': props.displayMode === 'grid'}">
        <h3 :class="['font-semibold text-gray-800 dark:text-white', props.displayMode === 'grid' ? 'text-md mb-1' : 'text-lg']">{{ sound.name }}</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          Duration: {{ formattedDuration }}
        </p>
      </div>
    </div>

    <div :class="footerClasses">
      <button
        type="button"
        @click.prevent="toggleFavoritePlaceholder"
        title="Toggle Favorite"
        class="p-2 rounded-md hover:text-red-500 dark:hover:text-red-400 focus:outline-none transition-colors duration-150 ease-in-out cursor-pointer"
        :class="isFavoritePlaceholder ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
        </svg>
        <span class="sr-only">Toggle Favorite</span>
      </button>
      <NuxtLink v-if="props.showCategoryLink && sound.category"
             :to="`/categories/${sound.category.id}`"
             :class="['text-xs text-indigo-500 dark:text-indigo-400 hover:underline', props.displayMode === 'grid' ? 'block mt-1' : 'ml-2']">
        {{ sound.category.name }}
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, computed, onMounted, onUnmounted, watch, defineEmits } from 'vue';

const props = defineProps({
  sound: {
    type: Object,
    required: true,
  },
  playingSoundId: {
    type: [String, Number, null],
    default: null,
  },
  displayMode: {
    type: String,
    default: 'list', // 'list' or 'grid'
  },
  showCategoryLink: {
    type: Boolean,
    default: true,
  },
  isFavorite: { // New prop to indicate if the sound is a favorite
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['sound-play-requested', 'sound-pause-requested', 'sound-ended', 'toggle-favorite']); // Added 'toggle-favorite'

const audio = ref(null);
const internalIsPlaying = ref(false); // Tracks if this specific audio instance is playing
const duration = ref(props.sound.duration_seconds || 0); // Expect duration in seconds

// No longer a placeholder, directly use the prop
// const isFavoritePlaceholder = ref(false);

const formattedDuration = computed(() => {
  const totalSeconds = Number(duration.value);
  if (isNaN(totalSeconds) || totalSeconds === 0) return 'N/A';
  const minutes = Math.floor(totalSeconds / 60);
  const seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
  return `${minutes}:${seconds}`;
});

// This computed property determines if this sound is the one currently marked as playing globally
const isCurrentlyPlaying = computed(() => props.playingSoundId === props.sound.id && internalIsPlaying.value);

onMounted(() => {
  if (props.sound.file_url) {
    audio.value = new Audio(props.sound.file_url);
    audio.value.addEventListener('ended', handleSoundEnded);
    audio.value.addEventListener('loadedmetadata', () => {
      if (audio.value) {
        duration.value = audio.value.duration;
      }
    });
    // Preload metadata
    audio.value.preload = 'metadata';
  }
});

onUnmounted(() => {
  if (audio.value) {
    audio.value.removeEventListener('ended', handleSoundEnded);
    audio.value.pause();
    audio.value = null;
  }
});

function togglePlayback() {
  if (!audio.value) return;

  if (internalIsPlaying.value) { // If this sound is currently playing, pause it
    audio.value.pause();
    internalIsPlaying.value = false;
    emit('sound-pause-requested', props.sound.id);
  } else { // If this sound is not playing, request to play it
    // Parent component will handle stopping other sounds
    emit('sound-play-requested', { id: props.sound.id, audioInstance: audio.value });
    // internalIsPlaying will be set to true once parent confirms playback via playingSoundId prop change
  }
}

function handleSoundEnded() {
  internalIsPlaying.value = false;
  emit('sound-ended', props.sound.id);
}

function handleToggleFavorite() {
  emit('toggle-favorite', props.sound.id);
}

// Watch for changes in playingSoundId prop from parent
watch(() => props.playingSoundId, (newPlayingId) => {
  if (newPlayingId === props.sound.id) {
    // If this sound is now the globally playing sound, and it's not already playing internally, play it.
    if (!internalIsPlaying.value && audio.value) {
      audio.value.play().catch(error => console.error("Error playing sound:", error));
      internalIsPlaying.value = true;
    }
  } else {
    // If another sound is now playing globally, and this sound was playing, pause it.
    if (internalIsPlaying.value && audio.value) {
      audio.value.pause();
      internalIsPlaying.value = false;
    }
  }
});

// Dynamic classes based on display mode
const itemClasses = computed(() => ({
  'sound-item': true,
  'bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow duration-200 flex flex-col justify-between': props.displayMode === 'grid',
  'flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-md shadow-sm': props.displayMode === 'list'
}));

const clickableAreaClasses = computed(() => ({
  'flex-grow': true,
  'flex items-center space-x-3': props.displayMode === 'list',
  'flex flex-col items-center w-full': props.displayMode === 'grid', // Ensure button and text are centered in grid
}));

const playButtonClasses = computed(() => [
  props.displayMode === 'grid' ? 'w-full mb-2' : '',
  'flex items-center justify-center p-2 rounded-md cursor-pointer transition-colors duration-150 ease-in-out',
  isCurrentlyPlaying.value ? 'bg-green-500 text-white' : 'bg-indigo-500 hover:bg-indigo-600 text-white'
]);

const footerClasses = computed(() => ({
  'flex items-center': true,
  'mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 justify-between w-full': props.displayMode === 'grid', // Ensure footer spans width in grid
  'flex-shrink-0 flex-row-reverse': props.displayMode === 'list'
}));

// Expose methods for parent if direct control is needed (though event-based is preferred)
defineExpose({
  id: props.sound.id,
  pauseSound: () => {
    if (audio.value && internalIsPlaying.value) {
      audio.value.pause();
      internalIsPlaying.value = false;
    }
  },
  playSound: () => {
     if (audio.value && !internalIsPlaying.value) {
        audio.value.play().catch(error => console.error("Error playing sound:", error));
        internalIsPlaying.value = true;
     }
  }
});

</script>

<style scoped>
/* Minimal specific styles, relying on Tailwind utility classes */
.sound-item {
  /* Base class for easier selection if needed, though not strictly necessary with dynamic classes */
}
</style>
