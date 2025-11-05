<template>
  <div>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Analytics Pembelajaran
          </h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Monitor performa materi dan hasil belajar siswa
          </p>
        </div>

        <div class="flex items-center space-x-3">
          <select
            v-model="selectedPeriod"
            class="form-input max-w-xs"
            @change="loadAnalyticsData"
          >
            <option value="7">7 Hari Terakhir</option>
            <option value="30">30 Hari Terakhir</option>
            <option value="90">3 Bulan Terakhir</option>
            <option value="365">1 Tahun Terakhir</option>
          </select>

          <button
            @click="exportReport"
            :disabled="isLoading || analyticsData.length === 0"
            class="btn btn-outline"
          >
            <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-1" />
            Export Report
          </button>
        </div>
      </div>
    </template>

    <!-- Loading state -->
    <div v-if="isLoading" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="animate-pulse">
          <div class="bg-gray-200 dark:bg-gray-700 h-32 rounded-lg"></div>
        </div>
      </div>
    </div>

    <!-- Analytics content -->
    <div v-else-if="analyticsData.length > 0" class="space-y-6">
      <!-- Overview Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Total Quiz Responses"
          :value="totalResponses"
          icon="heroicons:clipboard-document-list"
          color="blue"
        />

        <StatCard
          title="Rata-rata Skor"
          :value="averageScore.toFixed(1)"
          icon="heroicons:chart-bar"
          color="green"
        />

        <StatCard
          title="Tingkat Penguasaan"
          :value="masteryPercentage.toFixed(0) + '%'"
          icon="heroicons:academic-cap"
          color="purple"
        />

        <StatCard
          title="Materi Aktif"
          :value="activeMaterials"
          icon="heroicons:book-open"
          color="indigo"
        />
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Trend Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Tren Performa
            </h3>
          </div>

          <div class="card-body">
            <div ref="performanceChartRef" class="h-64"></div>
          </div>
        </div>

        <!-- Topic Distribution Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Distribusi Topik
            </h3>
          </div>

          <div class="card-body">
            <div ref="topicChartRef" class="h-64"></div>
          </div>
        </div>
      </div>

      <!-- Material Performance Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Performa Materi
          </h3>
          <div class="flex items-center space-x-2">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari materi..."
              class="form-input max-w-xs"
            />
          </div>
        </div>

        <div class="card-body">
          <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Materi
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Mapel
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Responses
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Avg Score
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Mastery
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>

              <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="item in filteredMaterials"
                  :key="item.material.material_id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ item.material.material_title }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(item.material.date_release) }}
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ item.material.subject_name }}
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ item.analytics?.aggregates?.total_respondents || 0 }}
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm text-gray-900 dark:text-white">
                        {{ item.analytics?.aggregates?.avg_score?.toFixed(1) || 0 }}
                      </div>
                      <div class="ml-2">
                        <ScoreIndicator :score="item.analytics?.aggregates?.avg_score || 0" />
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <MasteryBadge :percentage="item.analytics?.aggregates?.mastery_percent || 0" />
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <button
                        @click="viewMaterialDetails(item.material.material_id)"
                        class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300"
                      >
                        <Icon name="heroicons:eye" class="w-4 h-4" />
                      </button>

                      <button
                        @click="generateDetailedReport(item.material.material_id)"
                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                      >
                        <Icon name="heroicons:document-text" class="w-4 h-4" />
                      </button>

                      <button
                        @click="downloadQuizResponses(item.material.material_id)"
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      >
                        <Icon name="heroicons:arrow-down-tray" class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="filteredMaterials.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada data analytics untuk periode ini
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Student Performance Details -->
      <div v-if="selectedMaterialAnalytics" class="card">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              Detail Performa Siswa
            </h3>
            <button
              @click="selectedMaterialAnalytics = null"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <Icon name="heroicons:x-mark" class="w-5 h-5" />
            </button>
          </div>
        </div>

        <div class="card-body">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Per Student Performance -->
            <div>
              <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                Performa per Siswa
              </h4>
              <div class="space-y-3 max-h-96 overflow-y-auto">
                <div
                  v-for="student in selectedMaterialAnalytics.per_student"
                  :key="student.email"
                  class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ student.email }}
                    </div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                      {{ student.score }}%
                    </div>
                  </div>

                  <div v-if="student.topics_weak?.length" class="mb-2">
                    <div class="text-xs text-red-600 dark:text-red-400 font-medium mb-1">
                      Topik perlu diperhatikan:
                    </div>
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="topic in student.topics_weak"
                        :key="topic"
                        class="badge badge-danger text-xs"
                      >
                        {{ topic }}
                      </span>
                    </div>
                  </div>

                  <div v-if="student.recommendations?.length" class="text-xs text-gray-600 dark:text-gray-400">
                    <div class="font-medium mb-1">Rekomendasi:</div>
                    <ul class="list-disc list-inside space-y-1">
                      <li v-for="rec in student.recommendations" :key="rec">
                        {{ rec }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Per Topic Analysis -->
            <div>
              <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                Analisis per Topik
              </h4>
              <div class="space-y-3 max-h-96 overflow-y-auto">
                <div
                  v-for="topic in selectedMaterialAnalytics.per_topic"
                  :key="topic.topic"
                  class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ topic.topic }}
                    </div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                      {{ topic.avg_score?.toFixed(1) }}%
                    </div>
                  </div>

                  <div class="mb-2">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                      <div
                        class="bg-gradient-to-r from-red-500 via-yellow-500 to-green-500 h-2 rounded-full"
                        :style="{ width: `${topic.avg_score}%` }"
                      ></div>
                    </div>
                  </div>

                  <div v-if="topic.improvement_suggestion" class="text-xs text-gray-600 dark:text-gray-400">
                    <div class="font-medium mb-1">Saran perbaikan:</div>
                    {{ topic.improvement_suggestion }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No data state -->
    <div v-else class="text-center py-12">
      <Icon name="heroicons:chart-bar" class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada data analytics</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Data analytics akan muncul setelah siswa mengerjakan quiz.
      </p>
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
  title: 'Analytics Pembelajaran',
})

// Load Google Charts
const { $google } = useNuxtApp()

// Get auth and API composables
const auth = useAuth()
const { analytics } = useApi()

// Reactive state
const isLoading = ref(false)
const analyticsData = ref([])
const selectedPeriod = ref('30')
const searchQuery = ref('')
const selectedMaterialAnalytics = ref(null)

// Chart refs
const performanceChartRef = ref(null)
const topicChartRef = ref(null)

// Computed properties
const totalResponses = computed(() => {
  return analyticsData.value.reduce((sum, item) => {
    return sum + (item.analytics?.aggregates?.total_respondents || 0)
  }, 0)
})

const averageScore = computed(() => {
  const scores = analyticsData.value.map(item => item.analytics?.aggregates?.avg_score || 0)
  return scores.length > 0 ? scores.reduce((a, b) => a + b, 0) / scores.length : 0
})

const masteryPercentage = computed(() => {
  const masteryLevels = analyticsData.value.map(item => item.analytics?.aggregates?.mastery_percent || 0)
  return masteryLevels.length > 0 ? masteryLevels.reduce((a, b) => a + b, 0) / masteryLevels.length : 0
})

const activeMaterials = computed(() => {
  return analyticsData.value.filter(item =>
    item.analytics?.aggregates?.total_respondents > 0
  ).length
})

const filteredMaterials = computed(() => {
  if (!searchQuery.value) return analyticsData.value

  const query = searchQuery.value.toLowerCase()
  return analyticsData.value.filter(item =>
    item.material.material_title.toLowerCase().includes(query) ||
    item.material.subject_name.toLowerCase().includes(query)
  )
})

// Load analytics data
const loadAnalyticsData = async () => {
  try {
    isLoading.value = true

    const response = await analytics.dashboard({
      period: selectedPeriod.value,
    })

    if (response.success) {
      analyticsData.value = response.data

      // Wait for DOM to be ready before drawing charts
      await nextTick()
      drawCharts()
    }

  } catch (error) {
    console.error('Analytics load error:', error)
  } finally {
    isLoading.value = false
  }
}

// Draw Google Charts
const drawCharts = () => {
  drawPerformanceChart()
  drawTopicChart()
}

const drawPerformanceChart = () => {
  if (!performanceChartRef.value || !$google.charts) return

  const data = new $google.visualization.DataTable()
  data.addColumn('string', 'Materi')
  data.addColumn('number', 'Rata-rata Skor')
  data.addColumn('number', 'Tingkat Penguasaan')

  const chartData = analyticsData.value.map(item => [
    item.material.material_title.length > 20
      ? item.material.material_title.substring(0, 20) + '...'
      : item.material.material_title,
    item.analytics?.aggregates?.avg_score || 0,
    item.analytics?.aggregates?.mastery_percent || 0,
  ])

  data.addRows(chartData)

  const options = {
    chart: {
      title: 'Performa Materi',
      subtitle: 'Skor rata-rata vs tingkat penguasaan',
    },
    bars: 'horizontal',
    axes: {
      x: {
        0: { side: 'top', label: 'Skor' },
      },
    },
    bar: { groupWidth: '75%' },
    isStacked: true,
    colors: ['#3b82f6', '#8b5cf6'],
    legend: { position: 'top' },
    height: 300,
  }

  const chart = new $google.charts.Bar(performanceChartRef.value)
  chart.draw(data, $google.charts.Bar.convertOptions(options))
}

const drawTopicChart = () => {
  if (!topicChartRef.value || !$google.charts) return

  const data = new $google.visualization.DataTable()
  data.addColumn('string', 'Topik')
  data.addColumn('number', 'Jumlah Siswa')

  // Aggregate topic data across all materials
  const topicCounts = {}
  analyticsData.value.forEach(item => {
    if (item.analytics?.per_topic) {
      item.analytics.per_topic.forEach(topic => {
        if (!topicCounts[topic.topic]) {
          topicCounts[topic.topic] = 0
        }
        topicCounts[topic.topic] += item.analytics.aggregates.total_respondents || 0
      })
    }
  })

  const chartData = Object.entries(topicCounts)
    .sort(([,a], [,b]) => b - a)
    .slice(0, 10)
    .map(([topic, count]) => [topic, count])

  data.addRows(chartData)

  const options = {
    title: 'Top 10 Topik Paling Banyak Dikerjakan',
    pieHole: 0.4,
    colors: ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
    height: 300,
    chartArea: { width: '90%', height: '80%' },
    legend: { position: 'bottom' },
  }

  const chart = new $google.visualization.PieChart(topicChartRef.value)
  chart.draw(data, options)
}

// Action methods
const viewMaterialDetails = async (materialId) => {
  try {
    const response = await analytics.quizAnalysis(materialId)
    if (response.success) {
      selectedMaterialAnalytics.value = response.data
    }
  } catch (error) {
    console.error('Failed to load material details:', error)
  }
}

const generateDetailedReport = async (materialId) => {
  try {
    // Generate comprehensive report
    const material = analyticsData.value.find(item => item.material.material_id === materialId)
    if (material) {
      downloadReport(material)
    }
  } catch (error) {
    console.error('Failed to generate report:', error)
  }
}

const downloadQuizResponses = async (materialId) => {
  try {
    // Download quiz responses as CSV
    const material = analyticsData.value.find(item => item.material.material_id === materialId)
    if (material && material.analytics?.per_student) {
      downloadCSV(material.analytics.per_student, `${materialId}_responses.csv`)
    }
  } catch (error) {
    console.error('Failed to download responses:', error)
  }
}

const exportReport = () => {
  if (analyticsData.value.length === 0) return

  const reportData = {
    generated_at: new Date().toISOString(),
    period: selectedPeriod.value + ' days',
    summary: {
      total_materials: analyticsData.value.length,
      total_responses: totalResponses.value,
      average_score: averageScore.value,
      mastery_percentage: masteryPercentage.value,
    },
    materials: analyticsData.value,
  }

  const blob = new Blob([JSON.stringify(reportData, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `analytics_report_${new Date().toISOString().split('T')[0]}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

const downloadReport = (material) => {
  const report = {
    material: material.material,
    analytics: material.analytics,
    generated_at: new Date().toISOString(),
  }

  const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `${material.material.material_id}_report.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

const downloadCSV = (data, filename) => {
  const headers = Object.keys(data[0]).join(',')
  const rows = data.map(row => Object.values(row).join(','))
  const csv = [headers, ...rows].join('\n')

  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

// Initialize Google Charts
onMounted(async () => {
  if (process.client && $google) {
    $google.charts.load('current', { packages: ['bar', 'corechart'] })
    $google.charts.setOnLoadCallback(() => {
      loadAnalyticsData()
    })
  } else {
    loadAnalyticsData()
  }
})
</script>