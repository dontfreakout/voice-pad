<template>
  <div class="space-y-6">
    <div v-if="pendingCategoryData && !categoryData">Loading category details...</div>
    <div v-else-if="categoryError">Error loading category: {{ categoryError.message }}</div>
    <div v-else-if="categoryData && categoryData.category">
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ categoryData.category.name }}</h1>
        <NuxtLink to="/" class="text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to Home</NuxtLink>
      </div>

      <p v-if="categoryData.category.description" class="text-gray-700 dark:text-gray-300 mb-6">{{ categoryData.category.description }}</p>

      <div v-if="pendingSounds && !displayedSounds.length" class="text-center py-4">Loading sounds...</div>
      <div v-else-if="soundFetchError" class="text-red-500 dark:text-red-400">Error loading sounds: {{ soundFetchError.message }}</div>
      <div v-else-if="displayedSounds.length > 0">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Sounds</h2>
        <div :class="{ 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4': uiStore.getDisplayMode === 'grid', 'space-y-2': uiStore.getDisplayMode === 'list' }">
          <SoundItem v-for="sound in displayedSounds"
                     :key="sound.id"
                     :sound="sound"
                     :showCategoryLink="false"
                     />
                     <!-- SoundItem now gets its state (playing, favorite, displayMode) from Pinia stores directly -->
        </div>
        <!-- Pagination for sounds -->
         <div class="mt-8" v-if="paginationMeta && paginationMeta.links && paginationMeta.total > paginationMeta.per_page">
            <button v-for="link in paginationMeta.links" :key="link.label"
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
     <div v-else-if="!pendingCategoryData" class="text-center py-10">
      <p class="text-xl text-gray-500 dark:text-gray-400">Category not found.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute, onBeforeRouteUpdate } from 'vue-router'; // Import onBeforeRouteUpdate for Nuxt 3
import { useUiStore } from '~/stores/ui';
// useFavoritesStore is not directly needed here if SoundItem handles its own favorite logic via the store.
// However, initFavoriteSoundIds is called by the plugin.
import SoundItem from '~/components/SoundItem.vue';
import type { Sound, Category } from '~/types';

const uiStore = useUiStore();
const route = useRoute();

const categoryData = ref(null); // Holds { category: Category, sounds: Sound[] } or similar from main API
const pendingCategoryData = ref(true);
const categoryError = ref(null);

const displayedSounds = ref([]); // Holds the sounds for the current page (pagination)
const paginationMeta = ref(null); // For pagination links
const pendingSounds = ref(false); // Specifically for pagination loading state
const soundFetchError = ref(null); // Specifically for pagination/sound fetch errors

const categoryId = ref(route.params.id);
const currentPage = ref(route.query.page ? parseInt(route.query.page) : 1);


async function fetchCategoryAndSoundsData(catId, page = 1) {
  pendingCategoryData.value = page === 1; // Only true pending for initial load of category
  pendingSounds.value = true;
  categoryError.value = null;
  soundFetchError.value = null;

  try {
    // Using $fetch which is auto-imported in Nuxt 3 / available globally
    // Assuming API returns category details and first page of sounds together,
    // or just sounds if page > 1
    const { data: result, error: fetchErrorVal, pending: pendingVal } = await useFetch(`/api/categories/${catId}/sounds?page=${page}`, {
      key: `category-${catId}-page-${page}`, // Unique key for this request
      // initialCache: false, // Consider caching strategy
    });

    // This watcher pattern for useFetch is a bit unusual here.
    // Typically, you'd await useFetch and then work with its reactive data/error/pending refs.
    // Let's simplify to directly use the reactive refs from useFetch.

    if (fetchErrorVal.value) throw fetchErrorVal.value;

    // Assuming result.value has a structure like:
    // { category: Category, sounds: { data: Sound[], meta: PaginationMeta } } for page 1
    // OR { sounds: { data: Sound[], meta: PaginationMeta } } for subsequent pages

    if (result.value) {
      if (page === 1 && result.value.category) {
        categoryData.value = { category: result.value.category };
      }
      if (result.value.sounds) {
        displayedSounds.value = result.value.sounds.data || [];
        paginationMeta.value = result.value.sounds.meta || null;
      } else if (Array.isArray(result.value.data)) { // Fallback if sounds are in result.data directly
        displayedSounds.value = result.value.data;
        paginationMeta.value = result.value.meta || null;
      }
    } else {
      // Handle case where result.value is null (e.g. 404 for category not found on initial load)
       if (page === 1) categoryData.value = null; // Clear category data if not found
       displayedSounds.value = [];
       paginationMeta.value = null;
    }

  } catch (e) {
    console.error("Error fetching category/sounds data:", e);
    if (page === 1) categoryError.value = e; else soundFetchError.value = e;
    displayedSounds.value = []; // Clear sounds on error
    if (page === 1) categoryData.value = null;
  } finally {
    pendingCategoryData.value = false;
    pendingSounds.value = false;
  }
}

function fetchPaginatedSounds(url) {
  if (url) {
    const urlParams = new URLSearchParams(new URL(url).search);
    const newPage = parseInt(urlParams.get('page') || '1', 10);
    currentPage.value = newPage;
    // Update route query param for history/bookmarks, without triggering full navigation if possible
    // This might be better handled by NuxtLink pagination or router.push for query changes
    // For now, just fetch:
    fetchCategoryAndSoundsData(categoryId.value, newPage);
  }
}

onMounted(() => {
  fetchCategoryAndSoundsData(categoryId.value, currentPage.value);
});

// Handle route parameter changes (e.g., navigating from one category to another directly)
onBeforeRouteUpdate(async (to, from) => {
  if (to.params.id !== from.params.id) {
    categoryId.value = to.params.id;
    currentPage.value = to.query.page ? parseInt(to.query.page) : 1;
    // Reset states before fetching new category data
    categoryData.value = null;
    displayedSounds.value = [];
    paginationMeta.value = null;
    await fetchCategoryAndSoundsData(categoryId.value, currentPage.value);
  } else if (to.query.page !== from.query.page) {
    // Handle only page changes if category ID is the same
    currentPage.value = to.query.page ? parseInt(to.query.page) : 1;
    await fetchCategoryAndSoundsData(categoryId.value, currentPage.value);
  }
});


// Removed local displayMode (uiStore.getDisplayMode is used in template)
// Removed local favoriteSoundIds and related logic (SoundItem uses favoritesStore)
// Removed currentlyPlayingSoundId and playback handling (SoundItem uses uiStore)
// Removed window event listeners (handled by stores/plugins)
</script>

<style scoped>
/* Add any page-specific styles if needed */
</style>
