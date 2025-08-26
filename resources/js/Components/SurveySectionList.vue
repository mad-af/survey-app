<template>
  <div class="card card-sm bg-base-100 shadow-lg">
    <div class="card-body p-4">
      <div class="flex justify-between items-center">
        <h2 class="card-title text-lg mb-3 flex items-center gap-2">
          <ListOrdered class="h-5 w-5" />
          Survey Section
        </h2>
        <div class="tooltip" data-tip="Manage survey sections">
          <button class="btn btn-sm btn-base text-xs gap-2">
            <span class="font-medium">Manage Sections</span>
            <ArrowRight class="h-4 w-4" />
          </button>
        </div>
      </div>
      
      <!-- Sections List -->
      <div v-if="sortedSections.length > 0" class="space-y-3">
        <div 
          v-for="section in sortedSections" 
          :key="section.id"
          tabindex="0" 
          class="collapse collapse-open bg-base-100 border-base-300 border"
        >
          <div class="collapse-title font-semibold text-sm flex items-center gap-2">
            <span class="badge badge-sm badge-primary">{{ section.order }}</span>
            {{ section.title }}
          </div>
          <div class="collapse-content text-xs">
            <p v-if="section.description" class="text-base-content/70">{{ section.description }}</p>
            <p v-else class="text-base-content/50 italic">Tidak ada deskripsi</p>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="text-center py-8">
        <div class="flex flex-col items-center gap-1">
            <ListOrdered class="h-8 w-8 text-base-content" />
            <h3 class="font-medium text-base-content">Belum Ada Section Question Survey</h3>
            <span class="text-sm text-base-content/70">Klik "Manage Sections" untuk menambahkan section survey pertama Anda</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { ArrowRight, ListOrdered } from 'lucide-vue-next'

// Props
const props = defineProps({
  sections: {
    type: Array,
    default: () => []
  }
})

// Computed
const sortedSections = computed(() => {
  return [...props.sections].sort((a, b) => {
    const orderA = a.order || 0
    const orderB = b.order || 0
    return orderA - orderB
  })
})

</script>