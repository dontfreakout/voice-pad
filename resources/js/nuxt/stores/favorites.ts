import { defineStore } from 'pinia';
import type { Sound } from '~/types'; // Assuming Nuxt's alias `~` works for types in stores path

export const useFavoritesStore = defineStore('favorites', {
  state: () => ({
    favoriteSoundIds: [] as (string | number)[],
    favoriteSounds: [] as Sound[],
    isLoading: false,
    error: null as Error | null, // To store potential errors during fetch
  }),

  actions: {
    initFavoriteSoundIds() {
      if (typeof window !== 'undefined') {
        const storedIds = localStorage.getItem('favoriteSoundIds');
        if (storedIds) {
          try {
            const parsedIds = JSON.parse(storedIds);
            if (Array.isArray(parsedIds)) {
              this.favoriteSoundIds = parsedIds;
            } else {
              this.favoriteSoundIds = [];
              localStorage.setItem('favoriteSoundIds', JSON.stringify([])); // Correct potential corruption
            }
          } catch (e) {
            console.error("Error parsing favoriteSoundIds from localStorage", e);
            this.favoriteSoundIds = [];
            localStorage.setItem('favoriteSoundIds', JSON.stringify([])); // Reset on error
          }
        } else {
            // If nothing in localStorage, ensure the default empty array is there.
            localStorage.setItem('favoriteSoundIds', JSON.stringify(this.favoriteSoundIds));
        }
      }
    },

    _saveFavoriteSoundIdsToLocalStorage() {
      if (typeof window !== 'undefined') {
        localStorage.setItem('favoriteSoundIds', JSON.stringify(this.favoriteSoundIds));
        // Window event dispatch removed as components should rely on Pinia state
      }
    },

    addFavorite(soundId: string | number) {
      if (!this.favoriteSoundIds.includes(soundId)) {
        this.favoriteSoundIds.push(soundId);
        this._saveFavoriteSoundIdsToLocalStorage();
        // Optionally fetch all favorite sounds again to update the detailed list
        // this.fetchFavoriteSounds();
        // OR: if the sound object is available, add it directly to favoriteSounds
        // For now, let's rely on a separate call to fetchFavoriteSounds or manual refresh.
      }
    },

    removeFavorite(soundId: string | number) {
      const index = this.favoriteSoundIds.indexOf(soundId);
      if (index > -1) {
        this.favoriteSoundIds.splice(index, 1);
        this._saveFavoriteSoundIdsToLocalStorage();
        // Remove from the detailed favoriteSounds list as well
        this.favoriteSounds = this.favoriteSounds.filter(sound => sound.id !== soundId);
      }
    },

    toggleFavorite(soundId: string | number) {
      if (this.isFavorite(soundId)) {
        this.removeFavorite(soundId);
      } else {
        this.addFavorite(soundId);
      }
    },

    async fetchFavoriteSounds() {
      if (this.favoriteSoundIds.length === 0) {
        this.favoriteSounds = [];
        this.isLoading = false;
        this.error = null;
        return;
      }

      this.isLoading = true;
      this.error = null;
      try {
        // Note: useFetch is Nuxt 3 specific and might not be directly usable in Pinia stores
        // without extra setup or being passed in. A global fetch utility or direct fetch call is more common here.
        // For this example, let's assume a global $fetch is available as Nuxt 3 provides.
        // If this store is used outside Nuxt components (e.g. in plugins), direct `fetch` or `ofetch` from `ofetch` package is better.
        const idsQueryParam = this.favoriteSoundIds.join(',');
        const response = await $fetch<{ data: Sound[] } | Sound[]>(`/api/sounds?ids=${idsQueryParam}`, {
          // `key` option for `useFetch` is not applicable to `$fetch`.
          // Caching strategy for $fetch needs to be handled manually if desired, or rely on server caching.
        });

        if (Array.isArray(response)) {
            this.favoriteSounds = response;
        } else if (response && Array.isArray(response.data)) { // Common API wrapper { data: [...] }
            this.favoriteSounds = response.data;
        } else {
            console.warn("Unexpected response structure for favorite sounds:", response);
            this.favoriteSounds = []; // Default to empty on unexpected structure
        }

      } catch (e: any) {
        console.error("Error fetching favorite sounds:", e);
        this.error = e;
        this.favoriteSounds = []; // Clear sounds on error
      } finally {
        this.isLoading = false;
      }
    },

    // Action to add a sound object directly if it's already fetched and then favorited
    // This can avoid a re-fetch of all favorites if a sound object is available when favoriting.
    addSoundToFavoritesList(sound: Sound) {
        if (!this.favoriteSounds.find(s => s.id === sound.id)) {
            this.favoriteSounds.push(sound);
        }
    }
  },

  getters: {
    isFavorite(state) {
      return (soundId: string | number): boolean => {
        return state.favoriteSoundIds.includes(soundId);
      };
    },
    hasFavorites(state): boolean {
      return state.favoriteSoundIds.length > 0;
    },
    getFavoriteSounds(state): Sound[] {
      return state.favoriteSounds;
    },
    getIsLoading(state): boolean {
      return state.isLoading;
    },
    getFavoriteIds(state): (string | number)[] { // Getter for the IDs themselves
        return state.favoriteSoundIds;
    }
  },
});
