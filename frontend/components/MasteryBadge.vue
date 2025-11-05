<template>
  <div class="flex items-center space-x-2">
    <!-- Progress bar -->
    <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
      <div
        class="h-2 rounded-full transition-all duration-300"
        :class="progressBarClass"
        :style="{ width: `${percentage}%` }"
      ></div>
    </div>

    <!-- Percentage text -->
    <span
      :class="[
        'text-xs font-medium',
        textClass
      ]"
    >
      {{ Math.round(percentage) }}%
    </span>

    <!-- Icon for high mastery -->
    <Icon
      v-if="percentage >= 80"
      name="heroicons:star"
      class="w-4 h-4 text-yellow-500"
    />
  </div>
</template>

<script setup>
interface Props {
  percentage: number
}

const props = defineProps<Props>()

// Progress bar color class
const progressBarClass = computed(() => {
  if (props.percentage >= 80) return 'bg-green-500'
  if (props.percentage >= 60) return 'bg-blue-500'
  if (props.percentage >= 40) return 'bg-yellow-500'
  return 'bg-red-500'
})

// Text color class
const textClass = computed(() => {
  if (props.percentage >= 80) return 'text-green-600 dark:text-green-400'
  if (props.percentage >= 60) return 'text-blue-600 dark:text-blue-400'
  if (props.percentage >= 40) return 'text-yellow-600 dark:text-yellow-400'
  return 'text-red-600 dark:text-red-400'
})
</script>