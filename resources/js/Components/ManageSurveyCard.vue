<template>
  <div class="w-full shadow-sm card bg-base-100 card-sm">
    <div class="card-body">
      <div class="flex justify-between">
        <div>
          <h3 class="text-xs font-semibold tracking-wider uppercase text-base-content/60">
            <span>#</span>
            Section {{ section.order || 1 }}
          </h3>
          <h2 class="text-lg card-title">{{ section.title || 'Untitled Section' }}</h2>
        </div>
        <div class="flex gap-2 items-start">
          <div class="tooltip" data-tip="Delete Section">
            <button 
              class="font-medium btn btn-xs text-error"
              @click="$emit('delete-section', section)"
            >
              <Trash2 class="w-3 h-3" />
            </button>
          </div>
          <div class="tooltip" data-tip="Edit Section">
            <button 
              class="font-medium btn btn-xs"
              @click="$emit('edit-section', section)"
            >
              <Edit3 class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>
      <p class="text-sm text-base-content/70">
        {{ section.description || 'No description available for this section.' }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { Trash2, Edit3 } from 'lucide-vue-next'

// Props
const props = defineProps({
  section: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      title: '',
      description: '',
      order: 1
    })
  }
})

// Emits
const emit = defineEmits([
  'edit-section',
  'delete-section'
])
</script>