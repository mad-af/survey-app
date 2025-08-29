<template>
  <div class="shadow-xl card card-sm bg-base-100">
    <div class="p-4 card-body">
      <h2 class="flex gap-2 items-center mb-3 text-lg card-title">
        <BarChart3 class="w-5 h-5" />
        Survey Statistics
      </h2>
      
      <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Total Responses</div>
          <div class="text-xl stat-value text-primary">{{ totalResponses || 0 }}</div>
          <div class="text-xs stat-desc">All responses</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Completed</div>
          <div class="text-xl stat-value text-success">{{ completedResponses || 0 }}</div>
          <div class="text-xs stat-desc">Finished responses</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">In Progress</div>
          <div class="text-xl stat-value text-warning">{{ inProgressResponses || 0 }}</div>
          <div class="text-xs stat-desc">Ongoing responses</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Survey Code</div>
          <div class="text-lg stat-value text-info">{{ surveyCode || '-' }}</div>
          <div class="text-xs stat-desc">Survey identifier</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { BarChart3 } from 'lucide-vue-next'

// Define props
defineProps({
  totalResponses: {
    type: Number,
    default: 0
  },
  completedResponses: {
    type: Number,
    default: 0
  },
  inProgressResponses: {
    type: Number,
    default: 0
  },
  surveyCode: {
    type: String,
    default: '-'
  }
})
</script>