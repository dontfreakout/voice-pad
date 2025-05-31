import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
  state: () => ({
    displayMode: 'list' as 'list' | 'grid',
    currentlyPlayingSoundId: null as number | string | null,
    currentAudioInstance: null as HTMLAudioElement | null,
  }),

  actions: {
    // Action to initialize displayMode from localStorage, typically called on client-side mount
    initDisplayMode() {
      if (typeof window !== 'undefined') {
        const storedMode = localStorage.getItem('displayMode');
        if (storedMode === 'list' || storedMode === 'grid') {
          this.displayMode = storedMode;
        } else {
          // If nothing in localStorage or invalid value, ensure default is saved.
          localStorage.setItem('displayMode', this.displayMode);
        }
      }
    },

    setDisplayMode(mode: 'list' | 'grid') {
      this.displayMode = mode;
      if (typeof window !== 'undefined') {
        localStorage.setItem('displayMode', mode);
        // Window event dispatch removed as components should rely on Pinia state
      }
    },

    setPlayingSound(soundId: number | string, audioInstance: HTMLAudioElement) {
      if (this.currentAudioInstance && this.currentAudioInstance !== audioInstance) {
        this.currentAudioInstance.pause();
        // Optional: Reset currentTime for the old audio instance if desired
        // this.currentAudioInstance.currentTime = 0;
      }

      this.currentlyPlayingSoundId = soundId;
      this.currentAudioInstance = audioInstance;

      // Ensure the new audio instance plays
      // Check if it's already playing to avoid interrupting if it's the same instance being resumed
      if (this.currentAudioInstance.paused) {
        this.currentAudioInstance.play().catch(error => {
          console.error("Error playing audio:", error);
          // If play fails, reset the store state
          if (this.currentlyPlayingSoundId === soundId) { // Check if it's still the current sound
             this.stopCurrentSound();
          }
        });
      }
    },

    stopCurrentSound() {
      if (this.currentAudioInstance) {
        this.currentAudioInstance.pause();
        // Optional: Reset currentTime
        // this.currentAudioInstance.currentTime = 0;
        this.currentAudioInstance = null;
      }
      this.currentlyPlayingSoundId = null;
    },

    pauseCurrentSound() {
      if (this.currentAudioInstance && !this.currentAudioInstance.paused) {
        this.currentAudioInstance.pause();
      }
    },

    resumeCurrentSound() {
      if (this.currentAudioInstance && this.currentAudioInstance.paused) {
        this.currentAudioInstance.play().catch(error => {
            console.error("Error resuming audio:", error);
        });
      }
    },

    // Called when an audio element finishes playing on its own
    handleSoundEnded(soundId: number | string) {
        if (this.currentlyPlayingSoundId === soundId) {
            this.currentAudioInstance = null;
            this.currentlyPlayingSoundId = null;
        }
    }
  },

  getters: {
    isPlaying(state) {
      return (soundId: number | string): boolean => {
        return state.currentlyPlayingSoundId === soundId && state.currentAudioInstance !== null && !state.currentAudioInstance.paused;
      };
    },
    isSomethingPlaying(state): boolean {
      return state.currentlyPlayingSoundId !== null && state.currentAudioInstance !== null && !state.currentAudioInstance.paused;
    },
    getDisplayMode(state): 'list' | 'grid' {
        return state.displayMode;
    },
    getCurrentlyPlayingSoundId(state): number | string | null {
        return state.currentlyPlayingSoundId;
    }
  },
});
