<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <AppHeader />
    <main class="py-8">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>
    <footer class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
      <p>&copy; 2023 My Nuxt App</p>
    </footer>
  </div>
</template>

<script setup>
import AppHeader from '~/components/AppHeader.vue';
</script>
