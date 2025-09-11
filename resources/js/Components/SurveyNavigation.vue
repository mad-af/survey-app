<template>
  <div class="pt-4 mt-6 border-t sm:pt-6 sm:mt-8 border-base-300">
    <!-- Mobile Layout -->
    <div class="block space-y-3 md:hidden">
      <!-- Progress Info -->
      <div class="px-2 text-xs text-center text-base-content/60">
        {{ answeredQuestions }} dari {{ totalQuestions }} pertanyaan dijawab
      </div>
      
      <!-- Buttons Container -->
      <div class="flex flex-col space-y-2">
        <button @click="handlePreviousSection" class="w-full btn btn-outline btn-sm" :disabled="!hasPreviousSection">
          <ChevronLeft class="mr-1 w-3 h-3" />
          <span class="text-xs">Sebelumnya</span>
        </button>
        
        <button @click="handleNextSection" class="w-full btn btn-primary btn-sm" :disabled="!canProceed">
          <span class="text-xs">{{ hasNextSection ? 'Selanjutnya' : 'Selesai' }}</span>
          <ChevronRight v-if="hasNextSection" class="ml-1 w-3 h-3" />
          <Check v-else class="ml-1 w-3 h-3" />
        </button>
      </div>
    </div>

    <!-- Desktop Layout -->
    <div class="hidden justify-between items-center md:flex">
      <button @click="handlePreviousSection" class="btn btn-outline btn-md" :disabled="!hasPreviousSection">
        <ChevronLeft class="mr-2 w-4 h-4" />
        <span class="text-sm">Sebelumnya</span>
      </button>

      <div class="px-4 text-sm text-center text-base-content/60">
        {{ answeredQuestions }} dari {{ totalQuestions }} pertanyaan dijawab
      </div>

      <button @click="handleNextSection" class="btn btn-primary btn-md" :disabled="!canProceed">
        <span class="text-sm">{{ hasNextSection ? 'Selanjutnya' : 'Selesai' }}</span>
        <ChevronRight v-if="hasNextSection" class="ml-2 w-4 h-4" />
        <Check v-else class="ml-2 w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ChevronLeft, ChevronRight, Check } from 'lucide-vue-next'

// Define props
const props = defineProps({
  answeredQuestions: {
    type: Number,
    default: 0
  },
  totalQuestions: {
    type: Number,
    default: 0
  },
  hasPreviousSection: {
    type: Boolean,
    default: false
  },
  hasNextSection: {
    type: Boolean,
    default: true
  },
  canProceed: {
    type: Boolean,
    default: true
  }
})

// Define emits
const emit = defineEmits([
  'previous-section',
  'next-section'
])

// Methods
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

const handlePreviousSection = () => {
  scrollToTop()
  emit('previous-section')
}

const handleNextSection = () => {
  scrollToTop()
  emit('next-section')
}
</script>