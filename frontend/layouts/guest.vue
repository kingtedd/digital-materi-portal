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

    <!-- Main content for guests -->
    <div v-else>
      <!-- Guest navigation (minimal) -->
      <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex items-center">
              <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                Portal Digitalisasi Materi
              </h1>
            </div>

            <div class="flex items-center space-x-4">
              <!-- Dark mode toggle -->
              <ColorModeButton />

              <!-- Login button -->
              <button
                v-if="!auth.isAuthenticated"
                @click="auth.loginWithGoogle"
                :disabled="auth.isLoading"
                class="btn btn-primary btn-sm"
              >
                <Icon name="heroicons:arrow-right-on-rectangle" class="w-4 h-4 mr-1" />
                Login
              </button>

              <!-- User menu (for authenticated users) -->
              <div v-else class="flex items-center space-x-3">
                <span class="text-sm text-gray-700 dark:text-gray-300">
                  {{ auth.user?.name }}
                </span>
                <button
                  @click="auth.logout"
                  class="btn btn-outline btn-sm"
                >
                  <Icon name="heroicons:arrow-left-on-rectangle" class="w-4 h-4 mr-1" />
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page content -->
      <main>
        <slot />
      </main>
    </div>

    <!-- Global notification -->
    <UNotifications />

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-500 dark:text-gray-400">
          <p>&copy; {{ new Date().getFullYear() }} Portal Digitalisasi Materi. All rights reserved.</p>
        </div>
      </div>
    </footer>
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
const handleError = (error) => {
  console.error('Layout error:', error)
}

// Provide error handler to child components
provide('handleError', handleError)
</script>