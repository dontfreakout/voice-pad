<template>
  <section>
    <h2 class="text-2xl font-semibold mb-4 text-gray-800 dark:text-white">Categories</h2>
    <div v-if="pending" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
      <!-- Skeleton Loader -->
      <div v-for="n in 6" :key="n" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow animate-pulse">
        <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
        <div class="h-3 bg-gray-300 dark:bg-gray-700 rounded w-1/2"></div>
      </div>
    </div>
    <div v-else-if="error" class="text-red-500">Error loading categories: {{ error.message }}</div>
    <div v-else-if="categories && categories.data && categories.data.length > 0">
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
        <NuxtLink v-for="category in categories.data" :key="category.id" :to="`/categories/${category.id}`"
                  class="block p-6 bg-white dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:drop-shadow-violet-600 rounded-lg shadow hover:shadow-lg transition-all duration-200 ease-in-out">
          <h3 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">{{ category.name }}</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ category.sounds_count }}
            sound{{ category.sounds_count !== 1 ? 's' : '' }}</p>
        </NuxtLink>
      </div>
      <!-- Pagination -->
      <div class="mt-8" v-if="categories.meta && categories.meta.links">
        <button v-for="link in categories.meta.links" :key="link.label"
                @click="fetchPaginatedCategories(link.url)"
                :disabled="!link.url || link.active"
                :class="{ 'bg-indigo-600 text-white': link.active, 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300': !link.active && link.url, 'text-gray-400 dark:text-gray-500': !link.url }"
                class="px-4 py-2 mx-1 rounded-md focus:outline-none disabled:opacity-50">
          <span v-html="link.label"></span>
        </button>
      </div>
    </div>
    <p v-else class="text-gray-600 dark:text-gray-400">No categories available.</p>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const categories = ref({ data: [], meta: {} });
const pending = ref(true);
const error = ref(null);
const currentPage = ref(1);

async function fetchCategories(url = `/api/categories?page=${currentPage.value}`) {
  pending.value = true;
  error.value = null;
  try {
    // Assuming useFetch is auto-imported by Nuxt
    const { data, error: fetchError } = await useFetch(url, { key: url }); // Add key to ensure reactivity on URL change

    if (fetchError.value) {
      throw fetchError.value;
    }
    categories.value = data.value; // Assuming API returns { data: [...], meta: { links: [...] } }
  } catch (e) {
    error.value = e;
  } finally {
    pending.value = false;
  }
}

function fetchPaginatedCategories(url) {
  if (url) {
    const urlParams = new URLSearchParams(new URL(url).search);
    currentPage.value = urlParams.get('page') || 1;
    fetchCategories(url);
  }
}

onMounted(() => {
  fetchCategories();
});
</script>
