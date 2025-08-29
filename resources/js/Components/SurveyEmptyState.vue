<template>
  <div class="py-12 text-center">
    <div class="flex flex-col items-center space-y-4">
      <HelpCircle class="w-16 h-16 text-base-content/30" />
      <div>
        <h3 class="text-lg font-medium text-base-content">{{ title }}</h3>
        <p class="mt-1 text-base-content/60">{{ description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { HelpCircle } from 'lucide-vue-next'

// Define props
const props = defineProps({
  title: {
    type: String,
    default: 'Tidak ada pertanyaan'
  },
  description: {
    type: String,
    default: 'Pilih section untuk melihat pertanyaan survey'
  }
})
</script>