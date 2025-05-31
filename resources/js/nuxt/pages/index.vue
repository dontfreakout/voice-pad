<template>
  <div class="space-y-8">
    <!-- Favorite Sounds Section -->
    <FavoriteSounds
      :playingSoundId="currentlyPlayingSoundId"
      :displayMode="displayMode"
      @update:playingSoundId="updatePlayingSoundId"
    />

    <!-- Categories Section -->
    <CategoryList />

    <!--
      Note: SoundItem instances are children of FavoriteSounds or CategoryShow pages.
      Playback control logic (play/pause/ended events) should be handled within those parent components
      or by FavoriteSounds itself if it directly uses SoundItem.
      This index page will primarily manage the currentlyPlayingSoundId for sounds played from FavoriteSounds.
      If CategoryList were to render SoundItems directly (it doesn't currently),
      then this page would also need to handle its sound events.
    -->
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import FavoriteSounds from '~/components/FavoriteSounds.vue';
import CategoryList from '~/components/CategoryList.vue';

const currentlyPlayingSoundId = ref(null);
const displayMode = ref('list'); // Default display mode

// Update playing sound ID
function updatePlayingSoundId(soundId) {
  // This function is called by @update:playingSoundId from FavoriteSounds
  // It means a sound within FavoriteSounds has either started or stopped.
  // If soundId is null, it means the sound stopped or ended.
  // If soundId is not null, it's the ID of the sound that just started.

  // If a sound is playing, and a new soundId comes (not null and different),
  // we might need to explicitly stop the old sound if FavoriteSounds doesn't handle it internally.
  // However, FavoriteSounds and CategoryShow pages are designed to manage their own children SoundItems,
  // including stopping a sound when another starts within their own scope.
  // This top-level currentlyPlayingSoundId is more for knowing *which* sound is playing globally.
  currentlyPlayingSoundId.value = soundId;
}


// Handle display mode changes
const handleDisplayModeChange = (event) => {
  displayMode.value = event.detail.mode;
};

onMounted(() => {
  // Initialize displayMode from localStorage
  const savedMode = localStorage.getItem('displayMode');
  if (savedMode) {
    displayMode.value = savedMode;
  }
  // Listen for display mode changes from the header
  window.addEventListener('display-mode-changed', handleDisplayModeChange);
});

onUnmounted(() => {
  window.removeEventListener('display-mode-changed', handleDisplayModeChange);
});

</script>

<style scoped>
/* Page-specific styles for index.vue */
</style>
