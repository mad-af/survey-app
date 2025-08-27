<template>
  <div class="shadow-lg card card-sm bg-base-100">
    <div class="p-4 card-body">
      <div class="flex justify-between items-center">
        <h2 class="flex gap-2 items-center mb-3 text-lg card-title">
          <ListOrdered class="w-5 h-5" />
          Survey Section
        </h2>
        <div class="tooltip" data-tip="Manage survey sections">
          <button class="gap-2 text-xs btn btn-sm btn-base" @click="navigateToManage">
            <span class="font-medium">Manage Survey</span>
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>
      </div>
      
      <!-- Sections List -->
      <div v-if="sortedSections.length > 0" class="space-y-3">
        <div 
          v-for="section in sortedSections" 
          :key="section.id"
          tabindex="0" 
          class="border collapse collapse-open bg-base-100 border-base-300"
        >
          <div class="flex gap-2 items-center text-sm font-semibold collapse-title">
            <span class="badge badge-sm badge-primary">{{ section.order }}</span>
            {{ section.title }}
          </div>
          <div class="text-xs collapse-content">
            <p v-if="section.description" class="text-base-content/70">{{ section.description }}</p>
            <p v-else class="italic text-base-content/50">Tidak ada deskripsi</p>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="py-8 text-center">
        <div class="flex flex-col gap-1 items-center">
            <span class="text-sm text-base-content/70">Klik "Manage Survey" untuk menambahkan section survey pertama Anda</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { ArrowRight, ListOrdered } from 'lucide-vue-next'

// Props
const props = defineProps({
  sections: {
    type: Array,
    default: () => []
  },
  surveyId: {
    type: [String, Number],
    required: true
  }
})

// Functions
const navigateToManage = () => {
  router.visit(`/dashboard/survey/${props.surveyId}/manage`)
}

// Computed
const sortedSections = computed(() => {
  return [...props.sections].sort((a, b) => {
    const orderA = a.order || 0
    const orderB = b.order || 0
    return orderA - orderB
  })
})

</script>