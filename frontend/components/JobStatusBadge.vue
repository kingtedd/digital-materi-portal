<template>
  <div class="flex items-center space-x-2">
    <span
      :class="[
        'badge',
        statusClasses.badge,
        'flex items-center space-x-1'
      ]"
    >
      <Icon
        v-if="status === 'processing'"
        name="heroicons:arrow-path"
        class="h-3 w-3 animate-spin text-blue-600 dark:text-blue-400"
      />
      <Icon
        v-else
        :name="statusClasses.icon"
        :class="[
          'h-3 w-3',
          statusClasses.iconColor
        ]"
      />
      <span>{{ statusText }}</span>
    </span>

    <!-- Action buttons for certain statuses -->
    <div v-if="showActions" class="flex items-center space-x-1">
      <button
        v-if="status === 'failed' && onRetry"
        @click="$emit('retry')"
        class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
        title="Coba lagi"
      >
        <Icon name="heroicons:arrow-clockwise" class="h-4 w-4" />
      </button>

      <button
        v-if="status === 'processing'"
        @click="$emit('view-details')"
        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        title="Lihat detail"
      >
        <Icon name="heroicons:eye" class="h-4 w-4" />
      </button>

      <button
        v-if="status === 'done'"
        @click="$emit('view-result')"
        class="p-1 text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors"
        title="Lihat hasil"
      >
        <Icon name="heroicons:check-circle" class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
interface Props {
  status: 'pending' | 'processing' | 'done' | 'failed'
  showActions?: boolean
}

interface Emits {
  (e: 'retry'): void
  (e: 'view-details'): void
  (e: 'view-result'): void
}

const props = withDefaults(defineProps<Props>(), {
  showActions: false,
})

defineEmits<Emits>()

// Status configurations
const statusConfig = {
  pending: {
    text: 'Menunggu',
    badge: 'status-pending',
    icon: 'heroicons:clock',
    iconColor: 'text-gray-600 dark:text-gray-400',
  },
  processing: {
    text: 'Diproses',
    badge: 'status-processing',
    icon: 'heroicons:arrow-path',
    iconColor: 'text-blue-600 dark:text-blue-400',
  },
  done: {
    text: 'Selesai',
    badge: 'status-completed',
    icon: 'heroicons:check-circle',
    iconColor: 'text-green-600 dark:text-green-400',
  },
  failed: {
    text: 'Gagal',
    badge: 'status-failed',
    icon: 'heroicons:x-circle',
    iconColor: 'text-red-600 dark:text-red-400',
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