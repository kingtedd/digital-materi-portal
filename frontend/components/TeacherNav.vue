<template>
  <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 lg:block lg:translate-x-0 lg:static lg:inset-0">
    <div class="flex flex-col h-full">
      <!-- Logo -->
      <div class="flex items-center h-16 px-6 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
          Portal Guru
        </h1>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Main navigation -->
        <div class="space-y-1">
          <UButton
            :to="{ name: 'teacher-dashboard' }"
            variant="ghost"
            :class="[
              'w-full justify-start',
              $route.name === 'teacher-dashboard' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <Icon name="heroicons:home" class="w-5 h-5 mr-3" />
            Dashboard
          </UButton>

          <UButton
            :to="{ name: 'teacher-materials' }"
            variant="ghost"
            :class="[
              'w-full justify-start',
              $route.name?.startsWith('teacher-materials') ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <Icon name="heroicons:book-open" class="w-5 h-5 mr-3" />
            Materi
          </UButton>

          <UButton
            :to="{ name: 'teacher-analytics' }"
            variant="ghost"
            :class="[
              'w-full justify-start',
              $route.name === 'teacher-analytics' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <Icon name="heroicons:chart-bar" class="w-5 h-5 mr-3" />
            Analytics
          </UButton>

          <UButton
            :to="{ name: 'teacher-jobs' }"
            variant="ghost"
            :class="[
              'w-full justify-start',
              $route.name === 'teacher-jobs' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            <Icon name="heroicons:cog" class="w-5 h-5 mr-3" />
            Proses
          </UButton>
        </div>

        <!-- Secondary navigation -->
        <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
          <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            Pengaturan
          </h3>
          <div class="mt-3 space-y-1">
            <UButton
              :to="{ name: 'teacher-profile' }"
              variant="ghost"
              :class="[
                'w-full justify-start',
                $route.name === 'teacher-profile' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
              ]"
            >
              <Icon name="heroicons:user" class="w-5 h-5 mr-3" />
              Profil
            </UButton>

            <button
              @click="auth.logout"
              class="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
              <Icon name="heroicons:arrow-left-on-rectangle" class="w-5 h-5 mr-3" />
              Logout
            </button>
          </div>
        </div>
      </nav>

      <!-- User info -->
      <div class="flex-shrink-0 p-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <img
              :src="auth.user?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user?.name || '')}&background=3b82f6&color=fff`"
              :alt="auth.user?.name"
              class="w-8 h-8 rounded-full"
            />
          </div>
          <div class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
              {{ auth.user?.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
              {{ auth.user?.email }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="isMobileMenuOpen"
      class="fixed inset-0 z-30 bg-gray-600 dark:bg-gray-900 bg-opacity-75 lg:hidden"
      @click="closeMobileMenu"
    ></div>
  </aside>
</template>

<script setup>
const auth = useAuth()

// Mobile menu state
const isMobileMenuOpen = ref(false)

// Close mobile menu
const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

// Handle escape key
onMounted(() => {
  const handleEscape = (e) => {
    if (e.key === 'Escape') {
      closeMobileMenu()
    }
  }

  document.addEventListener('keydown', handleEscape)

  onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape)
  })
})
</script>