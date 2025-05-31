<template>
  <div class="space-y-6">
    <div v-if="pending && !categoryData">Loading category details...</div>
    <div v-else-if="error">Error loading category: {{ error.message }}</div>
    <div v-else-if="categoryData && categoryData.category">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ categoryData.category.name }}</h1>
        <NuxtLink to="/" class="text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to Home</NuxtLink>
      </div>

      <p v-if="categoryData.category.description" class="text-gray-700 dark:text-gray-300 mb-6">{{ categoryData.category.description }}</p>

      <div v-if="pending && !categoryData.sounds" class="text-center py-4">Loading sounds...</div>
      <div v-else-if="categoryData.sounds && categoryData.sounds.data && categoryData.sounds.data.length > 0">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Sounds</h2>
        <div :class="{ 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4': displayMode === 'grid', 'space-y-2': displayMode === 'list' }">
          <SoundItem v-for="sound in categoryData.sounds.data"
                     :key="sound.id"
                     :sound="sound"
                     :playingSoundId="currentlyPlayingSoundId"
                     :displayMode="displayMode"
                     :showCategoryLink="false"
                     :isFavorite="isSoundFavorite(sound.id)"
                     @sound-play-requested="handlePlayRequest"
                     @sound-pause-requested="handlePauseRequest"
                     @sound-ended="handleSoundEnded"
                     @toggle-favorite="handleToggleFavorite"
                     ref="soundItemsRefs"
                     />
        </div>
        <!-- Pagination for sounds -->
         <div class="mt-8" v-if="categoryData.sounds.meta && categoryData.sounds.meta.links && categoryData.sounds.meta.total > categoryData.sounds.meta.per_page">
            <button v-for="link in categoryData.sounds.meta.links" :key="link.label"
                    @click="fetchPaginatedSounds(link.url)"
                    :disabled="!link.url || link.active"
                    :class="{ 'bg-indigo-600 text-white': link.active, 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': !link.active && link.url, 'text-gray-400 dark:text-gray-500 cursor-not-allowed': !link.url }"
                    class="px-4 py-2 mx-1 rounded-md focus:outline-none disabled:opacity-50 transition-colors">
              <span v-html="link.label"></span>
            </button>
        </div>
      </div>
      <p v-else class="text-gray-600 dark:text-gray-400">No sounds found in this category yet.</p>
    </div>
     <div v-else-if="!pending" class="text-center py-10">
      <p class="text-xl text-gray-500 dark:text-gray-400">Category not found.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import SoundItem from '~/components/SoundItem.vue'; // Import SoundItem

const route = useRoute();
const categoryData = ref(null); // Will hold { category: {...}, sounds: { data: [...], meta: {...} } }
const pending = ref(true);
const error = ref(null);
const categoryId = route.params.id;
const currentSoundsPage = ref(1);
const currentlyPlayingSoundId = ref(null);
const soundItemsRefs = ref([]); // To call methods on SoundItem instances
const favoriteSoundIds = ref([]); // For managing favorites

// Local displayMode, ideally this would come from a shared state (Pinia store or provide/inject)
const displayMode = ref('list');

onMounted(() => {
  const savedMode = localStorage.getItem('displayMode');
  if (savedMode) {
    displayMode.value = savedMode;
  }
  // Listen for display mode changes from the header
  window.addEventListener('display-mode-changed', (event) => {
    displayMode.value = event.detail.mode;
  });

  fetchCategoryDetails();

  loadFavoriteIdsFromLocalStorage();
  // Listen for global favorite changes emitted by other components (like FavoriteSounds.vue or other SoundItem instances)
  window.addEventListener('favorites-updated-globally', (event) => {
    if (event.detail && Array.isArray(event.detail.favoriteIds)) {
      favoriteSoundIds.value = event.detail.favoriteIds;
    }
  });
});

const loadFavoriteIdsFromLocalStorage = () => {
  const storedIds = localStorage.getItem('favoriteSoundIds');
  if (storedIds) {
    favoriteSoundIds.value = JSON.parse(storedIds);
  }
};

const saveFavoriteIdsToLocalStorage = () => {
  localStorage.setItem('favoriteSoundIds', JSON.stringify(favoriteSoundIds.value));
};

watch(favoriteSoundIds, saveFavoriteIdsToLocalStorage, { deep: true });

watch(() => route.params.id, (newId) => {
  // This watcher might not be strictly necessary if the page fully reloads on param change,
  // but good for handling client-side navigations to the same page with different params.
  if (newId && newId !== categoryId) { // Ensure newId is different to avoid re-fetch on same page load
    // categoryId = newId; // categoryId is a const from route.params.id, it will be updated on next route enter
    currentSoundsPage.value = 1; // Reset page for new category
    currentlyPlayingSoundId.value = null; // Stop sound when navigating
    fetchCategoryDetails();
  }
});


async function fetchCategoryDetails(url) {
  // If no URL is provided, construct the initial URL
  if (!url) {
    url = `/api/categories/${route.params.id}/sounds?page=${currentSoundsPage.value}`;
  }

  pending.value = true;
  // error.value = null; // Keep previous error for a moment if categoryData exists
  try {
    const { data, error: fetchError, pending: dataPending } = await useFetch(url, {
      key: url, // Ensures re-fetch when URL changes (e.g., pagination)
    });

    // Watch for data changes
    watch(data, (newData) => {
      if (newData) {
        if (currentSoundsPage.value === 1 || !categoryData.value?.category) {
           categoryData.value = newData;
        } else if (categoryData.value) {
          categoryData.value.sounds = newData.sounds || newData;
        }
      }
    }, { immediate: true }); // immediate: true to run watcher on initial data load

    // Watch for error changes
    watch(fetchError, (newError) => {
      if (newError) {
        error.value = newError;
      }
    }, { immediate: true });

    // Watch for pending state changes
    watch(dataPending, (newPending) => {
        pending.value = newPending;
    }, { immediate: true });

  } catch (e) {
    error.value = e;
    pending.value = false;
  }
}

function fetchPaginatedSounds(url) {
  if (url) {
    const urlParams = new URLSearchParams(new URL(url).search);
    currentSoundsPage.value = parseInt(urlParams.get('page') || '1', 10);
    // When paginating, we don't want to reset the currently playing sound unless it's not in the new page
    fetchCategoryDetails(url);
  }
}

function handlePlayRequest({ id, audioInstance }) {
  if (currentlyPlayingSoundId.value && currentlyPlayingSoundId.value !== id) {
    // Find the SoundItem instance of the currently playing sound and pause it
    const currentSoundItem = soundItemsRefs.value.find(item => item.id === currentlyPlayingSoundId.value);
    if (currentSoundItem && typeof currentSoundItem.pauseSound === 'function') {
      currentSoundItem.pauseSound();
    }
  }

  currentlyPlayingSoundId.value = id;
  // The SoundItem itself will call audioInstance.play() when its playingSoundId prop updates.
  // Or, we can call it here if direct control is preferred, but prop-driven is more Vue-idiomatic.
  // const newSoundItem = soundItemsRefs.value.find(item => item.id === id);
  // if (newSoundItem && typeof newSoundItem.playSound === 'function') {
  //   newSoundItem.playSound();
  // }
}

function handlePauseRequest(id) {
  if (currentlyPlayingSoundId.value === id) {
    // The SoundItem itself handles pausing its audio.
    // We just update the global state here.
    currentlyPlayingSoundId.value = null;
  }
}

function handleSoundEnded(id) {
  if (currentlyPlayingSoundId.value === id) {
    currentlyPlayingSoundId.value = null;
  }
}

function isSoundFavorite(soundId) {
  return favoriteSoundIds.value.includes(soundId);
}

function handleToggleFavorite(soundId) {
  const index = favoriteSoundIds.value.indexOf(soundId);
  if (index > -1) {
    favoriteSoundIds.value.splice(index, 1);
  } else {
    favoriteSoundIds.value.push(soundId);
  }
  // Dispatch a global event so other components (like FavoriteSounds.vue) can update
  window.dispatchEvent(new CustomEvent('favorites-updated-globally', {
    detail: { favoriteIds: [...favoriteSoundIds.value] }
  }));
}

</script>

<style scoped>
/* Add any page-specific styles if needed */
</style>
