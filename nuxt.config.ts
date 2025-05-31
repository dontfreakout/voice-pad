export default defineNuxtConfig({
  ssr: false, // Disable SSR as Laravel handles initial page load
  srcDir: 'resources/js/nuxt', // Specify Nuxt source directory
  vite: {
    // Vite config for Laravel integration
    // This might need further adjustments based on your specific setup
  },
  modules: [
    '@pinia/nuxt',
  ],
  // My Nuxt config
})
