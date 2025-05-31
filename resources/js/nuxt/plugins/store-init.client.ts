import { useUiStore } from '~/stores/ui';
import { useFavoritesStore } from '~/stores/favorites';

export default defineNuxtPlugin(async (/* nuxtApp */) => {
  if (process.client) {
    const uiStore = useUiStore();
    const favoritesStore = useFavoritesStore();

    uiStore.initDisplayMode();
    favoritesStore.initFavoriteSoundIds();

    // Optionally, you might want to fetch initial data for favorites
    // if there are favorite IDs after initialization.
    // However, this might also be triggered by components that display this data.
    // Example:
    // if (favoritesStore.hasFavorites) {
    //   await favoritesStore.fetchFavoriteSounds();
    // }
  }
});
