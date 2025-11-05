export default defineNuxtConfig({
  devtools: { enabled: true },

  // App configuration
  app: {
    head: {
      title: 'Portal Digitalisasi Materi',
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'description', content: 'Portal Digitalisasi Materi - Sistem manajemen materi pembelajaran digital' },
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' },
      ],
    },
  },

  // Modules
  modules: [
    '@nuxt/icon',
    '@nuxtjs/tailwindcss',
    '@nuxtjs/color-mode',
    '@pinia/nuxt',
    '@vueuse/nuxt',
  ],

  // CSS
  css: ['~/assets/css/main.css'],

  // Runtime config
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE_URL || 'http://localhost:8000/api',
      apiUrl: process.env.API_URL || 'http://localhost:8000',
      googleClientId: process.env.GOOGLE_CLIENT_ID || '',
      appVersion: '1.0.0',
    },
  },

  // TypeScript configuration
  typescript: {
    typeCheck: {
      eslint: true,
    },
  },

  // Build configuration
  build: {
    transpile: ['@headlessui/vue', '@heroicons/vue'],
  },

  // PostCSS configuration
  postcss: {
    plugins: {
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  // Vite configuration
  vite: {
    optimizeDeps: {
      include: ['@headlessui/vue', '@heroicons/vue'],
    },
  },

  // Server configuration
  server: {
    port: 3000,
    host: '0.0.0.0',
  },

  // Nitro configuration
  nitro: {
    experimental: {
      wasm: true,
    },
  },
})