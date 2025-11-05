<template>
  <div class="card hover:shadow-md transition-shadow duration-200">
    <div class="card-body">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div
            :class="[
              'inline-flex items-center justify-center p-3 rounded-lg',
              colorClasses.bg
            ]"
          >
            <Icon
              :name="icon"
              :class="[
                'h-6 w-6',
                colorClasses.text
              ]"
            />
          </div>
        </div>
        <div class="ml-5 w-0 flex-1">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              {{ title }}
            </dt>
            <dd class="flex items-baseline">
              <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ value }}
              </div>
              <div v-if="change" class="ml-2 flex items-baseline text-sm font-semibold">
                <Icon
                  :name="changeType === 'increase' ? 'heroicons:arrow-up' : 'heroicons:arrow-down'"
                  :class="[
                    'h-4 w-4 flex-shrink-0',
                    changeType === 'increase' ? 'text-green-500' : 'text-red-500'
                  ]"
                />
                <span
                  :class="[
                    'ml-1',
                    changeType === 'increase' ? 'text-green-500' : 'text-red-500'
                  ]"
                >
                  {{ change }}
                </span>
              </div>
            </dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
interface Props {
  title: string
  value: number | string
  icon: string
  color: 'blue' | 'green' | 'yellow' | 'red' | 'purple' | 'indigo' | 'gray'
  change?: string
  changeType?: 'increase' | 'decrease'
}

const props = withDefaults(defineProps<Props>(), {
  changeType: 'increase',
})

// Color classes
const colorClasses = computed(() => {
  const colors = {
    blue: {
      bg: 'bg-blue-100 dark:bg-blue-900/20',
      text: 'text-blue-600 dark:text-blue-400',
    },
    green: {
      bg: 'bg-green-100 dark:bg-green-900/20',
      text: 'text-green-600 dark:text-green-400',
    },
    yellow: {
      bg: 'bg-yellow-100 dark:bg-yellow-900/20',
      text: 'text-yellow-600 dark:text-yellow-400',
    },
    red: {
      bg: 'bg-red-100 dark:bg-red-900/20',
      text: 'text-red-600 dark:text-red-400',
    },
    purple: {
      bg: 'bg-purple-100 dark:bg-purple-900/20',
      text: 'text-purple-600 dark:text-purple-400',
    },
    indigo: {
      bg: 'bg-indigo-100 dark:bg-indigo-900/20',
      text: 'text-indigo-600 dark:text-indigo-400',
    },
    gray: {
      bg: 'bg-gray-100 dark:bg-gray-900/20',
      text: 'text-gray-600 dark:text-gray-400',
    },
  }

  return colors[props.color] || colors.gray
})
</script>