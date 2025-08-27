<template>
  <div class="shadow-xl card card-sm bg-base-100">
    <div class="p-4 card-body">
      <h2 class="flex gap-2 items-center mb-3 text-lg card-title">
        <BarChart3 class="w-5 h-5" />
        Statistics
      </h2>
      
      <div class="space-y-3">
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Total Sections</div>
          <div class="text-xl stat-value text-primary">{{ statistics?.totalSections || 0 }}</div>
          <div class="text-xs stat-desc">Survey sections</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Total Questions</div>
          <div class="text-xl stat-value text-secondary">{{ statistics?.totalQuestions || 0 }}</div>
          <div class="text-xs stat-desc">All questions</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Responses</div>
          <div class="text-xl stat-value text-accent">{{ statistics?.totalResponses || 0 }}</div>
          <div class="text-xs stat-desc">Total responses</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Completion Rate</div>
          <div class="text-xl stat-value text-success">{{ statistics?.completionRate || '0%' }}</div>
          <div class="text-xs stat-desc">Response rate</div>
        </div>
        
        <div class="p-3 rounded-lg stat bg-base-200">
          <div class="text-xs stat-title">Avg. Time</div>
          <div class="text-lg stat-value text-info">{{ statistics?.averageTime || '0m' }}</div>
          <div class="text-xs stat-desc">Completion time</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { BarChart3 } from 'lucide-vue-next'

const props = defineProps({
  statistics: {
    type: Object,
    default: () => ({
      totalSections: 0,
      totalQuestions: 0,
      totalResponses: 0,
      completionRate: 0,
      averageTime: 0
    })
  }
})

</script>