<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Loading overlay -->
    <div
      v-if="auth.isLoading"
      class="fixed inset-0 bg-white dark:bg-gray-900 z-50 flex items-center justify-center"
    >
      <div class="text-center">
        <div class="spinner w-8 h-8 mx-auto mb-4"></div>
        <p class="text-gray-600 dark:text-gray-400">Memuat...</p>
      </div>
    </div>

    <!-- Main content -->
    <div v-else-if="auth.isAuthenticated">
      <!-- Navigation -->
      <TeacherNav v-if="auth.isTeacher" />
      <AdminNav v-else-if="auth.isAdmin" />

      <!-- Page content -->
      <main :class="[
        'transition-all duration-300',
        auth.isTeacher ? 'ml-64' : 'ml-64'
      ]">
        <div class="py-6">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page header -->
            <div v-if="$slots.header" class="mb-8">
              <slot name="header" />
            </div>

            <!-- Page content -->
            <div>
              <slot />
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Auth required -->
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
      <div class="max-w-md w-full space-y-8">
        <div class="text-center">
          <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">
            Portal Digitalisasi Materi
          </h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Silakan login untuk melanjutkan
          </p>
        </div>

        <div class="mt-8">
          <button
            @click="auth.loginWithGoogle"
            :disabled="auth.isLoading"
            class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            {{ auth.isLoading ? 'Mengalihkan...' : 'Login dengan Google' }}
          </button>
        </div>

        <!-- Error message -->
        <div v-if="auth.error" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <Icon name="heroicons:exclamation-circle" class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800 dark:text-red-200">{{ auth.error }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Global notification -->
    <UNotifications />

    <!-- Error modal -->
    <UModal v-model="isErrorModalOpen" :ui="{ width: 'sm:max-w-md' }">
      <UCard>
        <template #header>
          <div class="flex items-center">
            <Icon name="heroicons:exclamation-triangle" class="h-6 w-6 text-red-500 mr-2" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Terjadi Kesalahan</h3>
          </div>
        </template>

        <div class="text-gray-600 dark:text-gray-400">
          {{ errorMessage }}
        </div>

        <template #footer>
          <div class="flex justify-end space-x-3">
            <UButton
              variant="outline"
              @click="isErrorModalOpen = false"
            >
              Tutup
            </UButton>
          </div>
        </template>
      </UCard>
    </UModal>
  </div>
</template>

<script setup>
// Set page metadata
useHead({
  title: 'Portal Digitalisasi Materi',
  meta: [
    { name: 'description', content: 'Portal Digitalisasi Materi - Sistem manajemen materi pembelajaran digital' },
  ],
})

// Get auth composable
const auth = useAuth()

// Error handling
const isErrorModalOpen = ref(false)
const errorMessage = ref('')

// Handle global errors
const handleError = (error) => {
  errorMessage.value = error.message || 'Terjadi kesalahan yang tidak diketahui'
  isErrorModalOpen.value = true
}

// Provide error handler to child components
provide('handleError', handleError)
</script>

<style scoped>
/* Ensure proper spacing when sidebar is present */
.main-with-sidebar {
  @apply ml-64;
}

@media (max-width: 768px) {
  .main-with-sidebar {
    @apply ml-0;
  }
}
</style>