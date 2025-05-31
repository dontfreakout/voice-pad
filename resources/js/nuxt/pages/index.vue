<template>
  <div class="space-y-8">
    <!-- Favorite Sounds Section -->
    <FavoriteSounds />
    <!-- FavoriteSounds component now uses Pinia stores directly for its state and for SoundItem interactions -->

    <!-- Categories Section -->
    <CategoryList />
    <!-- CategoryList renders links. SoundItems on category pages will use Pinia stores. -->
  </div>
</template>

<script setup>
// Pinia stores are initialized by the plugin (store-init.client.ts).
// Components (FavoriteSounds, SoundItem via CategoryList->CategoryPage) now use these stores directly.
// Therefore, this page no longer needs to manage currentlyPlayingSoundId or displayMode locally.

import FavoriteSounds from '~/components/FavoriteSounds.vue';
import CategoryList from '~/components/CategoryList.vue';

// No page-specific logic needed here for playback or display mode,
// as child components and stores handle this.
// The plugin store-init.client.ts handles calling:
// uiStore.initDisplayMode();
// favoritesStore.initFavoriteSoundIds();
// FavoriteSounds.vue handles fetching its own data based on store state.

</script>

<style scoped>
/* Page-specific styles for index.vue */
</style>
