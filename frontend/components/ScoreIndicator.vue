<template>
  <div class="flex items-center space-x-1">
    <!-- Score bar -->
    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
      <div
        class="h-2 rounded-full transition-all duration-300"
        :class="scoreBarClass"
        :style="{ width: `${Math.min(score, 100)}%` }"
      ></div>
    </div>

    <!-- Grade indicator -->
    <span
      :class="[
        'text-xs font-medium',
        gradeClass
      ]"
    >
      {{ grade }}
    </span>
  </div>
</template>

<script setup>
interface Props {
  score: number
  maxScore?: number
}

const props = withDefaults(defineProps<Props>(), {
  maxScore: 100,
})

// Calculate percentage score
const percentageScore = computed(() => {
  return Math.min((props.score / props.maxScore) * 100, 100)
})

// Determine grade
const grade = computed(() => {
  const score = percentageScore.value
  if (score >= 90) return 'A'
  if (score >= 80) return 'B'
  if (score >= 70) return 'C'
  if (score >= 60) return 'D'
  return 'E'
})

// Score bar color class
const scoreBarClass = computed(() => {
  const score = percentageScore.value
  if (score >= 80) return 'bg-green-500'
  if (score >= 60) return 'bg-yellow-500'
  if (score >= 40) return 'bg-orange-500'
  return 'bg-red-500'
})

// Grade text color class
const gradeClass = computed(() => {
  const score = percentageScore.value
  if (score >= 80) return 'text-green-600 dark:text-green-400'
  if (score >= 60) return 'text-yellow-600 dark:text-yellow-400'
  if (score >= 40) return 'text-orange-600 dark:text-orange-400'
  return 'text-red-600 dark:text-red-400'
})
</script>