<template>
  <div>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Dashboard Guru
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Selamat datang kembali, {{ auth.user?.name }}!
          </p>
        </div>

        <div class="flex items-center space-x-3">
          <UButton
            :to="{ name: 'teacher-materials-create' }"
            variant="primary"
          >
            <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
            Materi Baru
          </UButton>
        </div>
      </div>
    </template>

    <!-- Error state -->
    <div v-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <Icon name="heroicons:exclamation-circle" class="h-5 w-5 text-red-400" />
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
            Terjadi Kesalahan
          </h3>
          <p class="mt-1 text-sm text-red-700 dark:text-red-300">
            {{ error }}
          </p>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-else-if="isLoading" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="animate-pulse">
          <div class="bg-gray-200 dark:bg-gray-700 h-32 rounded-lg"></div>
        </div>
      </div>
    </div>

    <!-- Dashboard content -->
    <div v-else class="space-y-6">
      <!-- Statistics cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total Materi"
          :value="stats.total_materials"
          icon="heroicons:book-open"
          color="blue"
        />

        <StatCard
          title="Materi Terbit"
          :value="stats.published_materials"
          icon="heroicons:check-circle"
          color="green"
        />

        <StatCard
          title="Sedang Diproses"
          :value="stats.processing_materials"
          icon="heroicons:arrow-path"
          color="yellow"
        />

        <StatCard
          title="Menunggu Proses"
          :value="stats.waiting_materials"
          icon="heroicons:clock"
          color="gray"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Materials -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Materi Terbaru
            </h3>
            <UButton
              :to="{ name: 'teacher-materials' }"
              variant="ghost"
              size="sm"
            >
              Lihat Semua
            </UButton>
          </div>

          <div class="card-body">
            <div v-if="materials.length === 0" class="text-center py-8">
              <Icon name="heroicons:document-duplicate" class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada materi</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Mulai dengan membuat materi pembelajaran pertama Anda.
              </p>
              <div class="mt-6">
                <UButton
                  :to="{ name: 'teacher-materials-create' }"
                  variant="primary"
                >
                  <Icon name="heroicons:plus" class="w-4 h-4 mr-1" />
                  Buat Materi
                </UButton>
              </div>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="material in materials.slice(0, 5)"
                :key="material.material_id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
              >
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ material.material_title }}
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ material.subject_name }} • {{ formatDate(material.date_release) }}
                  </p>
                </div>

                <div class="ml-4">
                  <MaterialStatusBadge :status="material.status" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Jobs -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Proses Terbaru
            </h3>
            <UButton
              :to="{ name: 'teacher-jobs' }"
              variant="ghost"
              size="sm"
            >
              Lihat Semua
            </UButton>
          </div>

          <div class="card-body">
            <div v-if="recentJobs.length === 0" class="text-center py-8">
              <Icon name="heroicons:cog" class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada proses</h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Proses pembuatan konten digital akan muncul di sini.
              </p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="job in recentJobs"
                :key="job.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
              >
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ formatJobAction(job.action) }}
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatRelativeTime(job.created_at) }}
                  </p>
                </div>

                <div class="ml-4">
                  <JobStatusBadge :status="job.status" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Aksi Cepat
          </h3>
        </div>

        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <UButton
              :to="{ name: 'teacher-materials-create' }"
              variant="outline"
              class="justify-start"
            >
              <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
              Upload Materi Baru
            </UButton>

            <UButton
              :to="{ name: 'teacher-materials' }"
              variant="outline"
              class="justify-start"
            >
              <Icon name="heroicons:book-open" class="w-4 h-4 mr-2" />
              Kelola Materi
            </UButton>

            <UButton
              :to="{ name: 'teacher-analytics' }"
              variant="outline"
              class="justify-start"
            >
              <Icon name="heroicons:chart-bar" class="w-4 h-4 mr-2" />
              Lihat Analytics
            </UButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Set page metadata
definePageMeta({
  middleware: 'auth',
  layout: 'default',
})

useHead({
  title: 'Dashboard Guru',
})

// Get auth and API composables
const auth = useAuth()
const { materials } = useApi()

// Reactive state
const isLoading = ref(true)
const error = ref(null)

// Dashboard data
const stats = ref({
  total_materials: 0,
  published_materials: 0,
  processing_materials: 0,
  waiting_materials: 0,
  completed_jobs: 0,
  failed_jobs: 0,
  processing_jobs: 0,
})

const materials = ref([])
const recentJobs = ref([])

// Load dashboard data
const loadDashboardData = async () => {
  try {
    isLoading.value = true
    error.value = null

    // Load materials
    const materialsResponse = await materials.index()
    if (materialsResponse.success) {
      materials.value = materialsResponse.data

      // Calculate statistics
      const allMaterials = materialsResponse.data
      stats.value.total_materials = allMaterials.length
      stats.value.published_materials = allMaterials.filter(m => m.status === 'PUBLISHED').length
      stats.value.processing_materials = allMaterials.filter(m => m.status === 'PROCESSING').length
      stats.value.waiting_materials = allMaterials.filter(m => m.status === 'WAITING').length
    }

    // Load recent jobs (mock data for now, would come from API)
    // const jobsResponse = await jobs.index({ limit: 5 })
    // if (jobsResponse.success) {
    //   recentJobs.value = jobsResponse.data
    // }

  } catch (err) {
    console.error('Dashboard load error:', err)
    error.value = 'Gagal memuat data dashboard'
  } finally {
    isLoading.value = false
  }
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatRelativeTime = (dateString) => {
  if (!dateString) return '-'

  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / (1000 * 60))
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))

  if (diffMins < 1) return 'Baru saja'
  if (diffMins < 60) return `${diffMins} menit yang lalu`
  if (diffHours < 24) return `${diffHours} jam yang lalu`
  if (diffDays < 7) return `${diffDays} hari yang lalu`

  return formatDate(dateString)
}

const formatJobAction = (action) => {
  const actions = {
    'generate_digital': 'Generate Konten Digital',
    'create_classroom': 'Buat Classroom',
    'send_announcement': 'Kirim Pengumuman',
    'create_assignment': 'Buat Tugas',
  }

  return actions[action] || action
}

// Load data on component mount
onMounted(() => {
  if (auth.isAuthenticated.value) {
    loadDashboardData()
  }
})
</script>