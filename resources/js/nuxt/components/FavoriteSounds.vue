<template>
  <div v-if="favoriteSoundIds.length > 0">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Favorite Sounds</h2>
    <div v-if="pending">Loading favorite sounds...</div>
    <div v-else-if="error">Error loading favorite sounds: {{ error.message }}</div>
    <div v-else-if="favoriteSounds.length > 0"
         :class="{ 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4': displayMode === 'grid', 'space-y-2': displayMode === 'list' }">
      <SoundItem v-for="sound in favoriteSounds"
                 :key="sound.id"
                 :sound="sound"
                 :playingSoundId="playingSoundId"
                 :displayMode="displayMode"
                 :showCategoryLink="true"
                 :isFavorite="true" /* All sounds here are favorites */
                 @sound-play-requested="handlePlayRequest"
                 @sound-play-requested="handlePlayRequest"
                 @sound-pause-requested="handlePauseRequest"
                 @sound-ended="handleSoundEnded"
                 @toggle-favorite="handleToggleFavorite"
                 :ref="el => soundItemsRefs[sound.id] = el"
                 />
    </div>
    <p v-else-if="!pending && favoriteSoundIds.length > 0" class="text-gray-600 dark:text-gray-400">Could not load details for some favorite sounds, or they may no longer exist.</p>
  </div>
  <div v-else>
    <!-- Optionally, show a message if there are no favorites at all -->
    <!-- <p class="text-gray-600 dark:text-gray-400">You haven't added any sounds to your favorites yet.</p> -->
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from 'vue';
import SoundItem from '~/components/SoundItem.vue'; // Assuming SoundItem is in components

const props = defineProps({
  playingSoundId: {
    type: [String, Number, null],
    default: null,
  },
  displayMode: {
    type: String,
    default: 'list',
  }
});

const emit = defineEmits(['update:playingSoundId']);

const favoriteSoundIds = ref([]);
const favoriteSounds = ref([]);
const pending = ref(false);
const error = ref(null);
const soundItemsRefs = ref({}); // Use an object for easier ID-based access


const loadFavoriteIdsFromLocalStorage = () => {
  const storedIds = localStorage.getItem('favoriteSoundIds');
  if (storedIds) {
    favoriteSoundIds.value = JSON.parse(storedIds);
  }
};

const saveFavoriteIdsToLocalStorage = () => {
  localStorage.setItem('favoriteSoundIds', JSON.stringify(favoriteSoundIds.value));
};

onMounted(() => {
  loadFavoriteIdsFromLocalStorage();
  fetchFavoriteSoundsData();

  // Listen for global favorite changes if not handled by direct prop updates
  window.addEventListener('favorites-updated-globally', (event) => {
    favoriteSoundIds.value = event.detail.favoriteIds;
  });
});

watch(favoriteSoundIds, (newIds, oldIds) => {
  saveFavoriteIdsToLocalStorage();
  // Avoid fetching if only order changed or an item was removed but list still populated by other means
  // This simple comparison works if we always replace the array.
  if (JSON.stringify(newIds) !== JSON.stringify(oldIds)) {
      fetchFavoriteSoundsData();
  }
}, { deep: true });


async function fetchFavoriteSoundsData() {
  if (favoriteSoundIds.value.length === 0) {
    favoriteSounds.value = [];
    pending.value = false;
    return;
  }

  pending.value = true;
  error.value = null;
  try {
    // Assuming useFetch is auto-imported
    // The API endpoint /api/sounds?ids=1,2,3 needs to accept a comma-separated list of IDs
    const idsQueryParam = favoriteSoundIds.value.join(',');
    const { data, error: fetchError, pending: dataPending } = await useFetch(`/api/sounds?ids=${idsQueryParam}`, {
      key: `favorites-${idsQueryParam}`, // Unique key for caching and re-fetching
      // initialCache: false, // Consider if caching is appropriate here
    });

    watch(data, (newData) => {
      if (newData && newData.data) { // Assuming API returns { data: [...] }
        favoriteSounds.value = newData.data;
      } else if (newData) { // Fallback if API returns array directly
        favoriteSounds.value = newData;
      }
    }, { immediate: true });

    watch(fetchError, (newError) => {
      if(newError) error.value = newError;
    }, { immediate: true });

    watch(dataPending, (newPending) => {
      pending.value = newPending;
    }, { immediate: true });


  } catch (e) {
    error.value = e;
    pending.value = false;
  }
}

function handlePlayRequest(payload) { // payload is { id: soundId, audioInstance: ... }
  // Pause other currently playing sounds managed by this FavoriteSounds instance
  Object.values(soundItemsRefs.value).forEach(itemComponent => {
    if (itemComponent && itemComponent.id !== payload.id && typeof itemComponent.pauseSound === 'function') {
      itemComponent.pauseSound();
    }
  });
  // Notify parent (e.g., index.vue) about the sound that wants to play
  emit('update:playingSoundId', payload.id);
}

function handlePauseRequest(soundId) { // soundId is the ID of the sound that was paused
  // Notify parent if this was the sound currently tracked as playing by the parent
  if (props.playingSoundId === soundId) {
    emit('update:playingSoundId', null);
  }
}

function handleSoundEnded(soundId) { // soundId is the ID of the sound that ended
  // Notify parent if this was the sound currently tracked as playing by the parent
  if (props.playingSoundId === soundId) {
    emit('update:playingSoundId', null);
  }
}

// This function is called when SoundItem emits 'toggle-favorite'
function handleToggleFavorite(soundId) {
  const index = favoriteSoundIds.value.indexOf(soundId);
  if (index > -1) {
    favoriteSoundIds.value.splice(index, 1);
  } else {
    favoriteSoundIds.value.push(soundId);
  }
  // No need to call saveFavoriteIdsToLocalStorage() here as the watcher on favoriteSoundIds will do it.
  // Also, the watcher will trigger fetchFavoriteSoundsData if the list of IDs changes.

  // Dispatch a global event so other instances of SoundItem or other components can react
  window.dispatchEvent(new CustomEvent('favorites-updated-globally', {
    detail: { favoriteIds: [...favoriteSoundIds.value] }
  }));
}

// Clear refs when component unmounts or before re-render if list changes significantly
onUnmounted(() => {
  soundItemsRefs.value = {};
});
watch(favoriteSounds, () => { // Reset refs if the list of sounds itself changes
    soundItemsRefs.value = {};
});


// Expose for parent component if needed, e.g. for a global "clear favorites" button
defineExpose({
  refreshFavorites: fetchFavoriteSoundsData,
  favoriteSoundIds // Allow parent to see current IDs
});

</script>

<style scoped>
/* Scoped styles for FavoriteSounds component */
</style>
