<template>
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center gap-3">
      <div class="tooltip" data-tip="Back button">
        <button 
          v-if="showBackButton" 
          @click="goBack" 
          class="btn btn-ghost btn-sm"
          :disabled="!canGoBack"
        >
          <ArrowLeft class="w-4 h-4" />
        </button>
      </div>
      <div>
        <h1 class="text-xl font-bold text-base-content">{{ title }}</h1>
        <p v-if="description" class="text-sm text-base-content/60 mt-1">{{ description }}</p>
      </div>
    </div>
    <div v-if="$slots.action" class="flex gap-2">
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { ArrowLeft } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

// Props
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: null
  },
  showBackButton: {
    type: Boolean,
    default: false
  },
  backUrl: {
    type: String,
    default: null
  }
})

// Computed
const canGoBack = computed(() => {
  return window.history.length > 1
})

// Methods
const goBack = () => {
  if (props.backUrl) {
    router.visit(props.backUrl)
  } else {
    window.history.back()
  }
}
</script>