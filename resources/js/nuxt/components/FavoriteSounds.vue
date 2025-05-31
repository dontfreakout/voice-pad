<template>
  <div v-if="favoritesStore.hasFavorites">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Favorite Sounds</h2>
    <div v-if="favoritesStore.isLoading">Loading favorite sounds...</div>
    <div v-else-if="favoritesStore.error">Error loading favorite sounds: {{ favoritesStore.error.message }}</div>
    <div v-else-if="favoritesStore.getFavoriteSounds.length > 0"
         :class="{ 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4': uiStore.getDisplayMode === 'grid', 'space-y-2': uiStore.getDisplayMode === 'list' }">
      <SoundItem v-for="sound in favoritesStore.getFavoriteSounds"
                 :key="sound.id"
                 :sound="sound"
                 :showCategoryLink="true"
                 />
                 <!-- SoundItem now gets its state (playing, favorite, displayMode) from Pinia stores directly -->
    </div>
    <p v-else-if="!favoritesStore.isLoading && favoritesStore.favoriteSoundIds.length > 0" class="text-gray-600 dark:text-gray-400">
      Could not load details for some favorite sounds, or they may no longer exist.
    </p>
  </div>
  <div v-else>
    <!-- Optional: Message if no favorites at all. Controlled by v-if="favoritesStore.hasFavorites" -->
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useUiStore } from '~/stores/ui';
import { useFavoritesStore } from '~/stores/favorites';
import SoundItem from '~/components/SoundItem.vue';

const uiStore = useUiStore();
const favoritesStore = useFavoritesStore();

// Removed props for playingSoundId and displayMode, as SoundItem and this component will use Pinia.
// Removed emit for update:playingSoundId, as SoundItem interacts directly with uiStore.

onMounted(() => {
  // favoritesStore.initFavoriteSoundIds() is called by the plugin store-init.client.ts
  // So, we only need to ensure data is fetched if IDs are present.
  if (favoritesStore.hasFavorites && favoritesStore.getFavoriteSounds.length === 0) {
    favoritesStore.fetchFavoriteSounds();
  }
});

// Watch for changes in favorite IDs (e.g., after init or external localStorage change not caught by Pinia's reactivity immediately)
// and fetch sounds if the list of sounds is empty or doesn't match the IDs.
// Pinia's state is reactive, so components should update automatically if favoriteSounds array changes in store.
// This watcher might be for ensuring fetch happens after IDs are loaded and if sounds aren't already fetched.
watch(() => favoritesStore.favoriteSoundIds, (newIds) => {
  if (newIds.length > 0) {
    // Basic check: if IDs exist but detailed sounds don't, fetch.
    // More sophisticated checks could compare IDs in favoriteSounds vs favoriteSoundIds.
    if (favoritesStore.getFavoriteSounds.length !== newIds.length) {
       favoritesStore.fetchFavoriteSounds();
    }
  } else {
    // If IDs become empty, the store action fetchFavoriteSounds handles clearing favoriteSounds.
    // Or ensure it's cleared here too if needed:
    // favoritesStore.favoriteSounds = [];
    // Actually, fetchFavoriteSounds handles this.
  }
}, { deep: true, immediate: true }); // Immediate to run on component mount after store init

// Removed local state for favoriteSoundIds, favoriteSounds, pending, error.
// Removed localStorage logic (handled by store).
// Removed window event listener for 'favorites-updated-globally' (store is source of truth).
// Removed playback handling methods (handlePlayRequest, etc.) as SoundItem now uses uiStore.
// Removed soundItemsRefs as direct child manipulation for playback is no longer needed here.
</script>

<style scoped>
/* Scoped styles for FavoriteSounds component */
</style>
