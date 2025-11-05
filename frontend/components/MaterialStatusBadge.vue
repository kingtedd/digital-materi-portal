<template>
  <span
    :class="[
      'badge',
      statusClasses.badge,
      'flex items-center space-x-1'
    ]"
  >
    <Icon
      :name="statusClasses.icon"
      :class="[
        'h-3 w-3',
        statusClasses.iconColor
      ]"
    />
    <span>{{ statusText }}</span>
  </span>
</template>

<script setup>
interface Props {
  status: string
}

const props = defineProps<Props>()

// Status configurations
const statusConfig = {
  WAITING: {
    text: 'Menunggu',
    badge: 'status-waiting',
    icon: 'heroicons:clock',
    iconColor: 'text-yellow-600 dark:text-yellow-400',
  },
  PROCESSING: {
    text: 'Diproses',
    badge: 'status-processing',
    icon: 'heroicons:arrow-path',
    iconColor: 'text-blue-600 dark:text-blue-400',
  },
  PUBLISHED: {
    text: 'Terbit',
    badge: 'status-published',
    icon: 'heroicons:check-circle',
    iconColor: 'text-green-600 dark:text-green-400',
  },
  FAILED: {
    text: 'Gagal',
    badge: 'status-failed',
    icon: 'heroicons:x-circle',
    iconColor: 'text-red-600 dark:text-red-400',
  },
  PENDING: {
    text: 'Menunggu',
    badge: 'status-pending',
    icon: 'heroicons:clock',
    iconColor: 'text-gray-600 dark:text-gray-400',
  },
  COMPLETED: {
    text: 'Selesai',
    badge: 'status-completed',
    icon: 'heroicons:check-circle',
    iconColor: 'text-green-600 dark:text-green-400',
  },
}

const statusClasses = computed(() => {
  return statusConfig[props.status] || {
    text: props.status,
    badge: 'badge-secondary',
    icon: 'heroicons:question-mark-circle',
    iconColor: 'text-gray-600 dark:text-gray-400',
  }
})

const statusText = computed(() => statusClasses.value.text)
</script>