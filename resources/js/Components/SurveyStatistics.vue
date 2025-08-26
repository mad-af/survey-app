<template>
  <div class="card card-sm bg-base-100 shadow-xl">
    <div class="card-body p-4">
      <h2 class="card-title text-lg mb-3 flex items-center gap-2">
        <BarChart3 class="h-5 w-5" />
        Statistics
      </h2>
      
      <div class="space-y-1">
        <div class="stat bg-base-200 rounded-lg p-3">
          <div class="stat-title text-xs">Total Sections</div>
          <div class="stat-value text-xl text-primary">{{ statistics?.totalSections || 5 }}</div>
          <div class="stat-desc text-xs">Survey sections</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg p-3">
          <div class="stat-title text-xs">Total Questions</div>
          <div class="stat-value text-xl text-secondary">{{ statistics?.totalQuestions || 24 }}</div>
          <div class="stat-desc text-xs">All questions</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg p-3">
          <div class="stat-title text-xs">Responses</div>
          <div class="stat-value text-xl text-accent">{{ statistics?.totalResponses || 156 }}</div>
          <div class="stat-desc text-xs">Total responses</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg p-3">
          <div class="stat-title text-xs">Completion Rate</div>
          <div class="stat-value text-xl text-success">{{ formatPercentage(statistics?.completionRate) || '87%' }}</div>
          <div class="stat-desc text-xs">Response rate</div>
        </div>
        
        <div class="stat bg-base-200 rounded-lg p-3">
          <div class="stat-title text-xs">Avg. Time</div>
          <div class="stat-value text-lg text-info">{{ formatTime(statistics?.averageTime) || '12m' }}</div>
          <div class="stat-desc text-xs">Completion time</div>
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
      totalSections: 5,
      totalQuestions: 24,
      totalResponses: 156,
      completionRate: 87,
      averageTime: 12
    })
  }
})

const formatPercentage = (value) => {
  if (value === null || value === undefined) return null
  return `${Math.round(value)}%`
}

const formatTime = (minutes) => {
  if (minutes === null || minutes === undefined) return null
  
  if (minutes < 60) {
    return `${Math.round(minutes)}m`
  } else {
    const hours = Math.floor(minutes / 60)
    const remainingMinutes = Math.round(minutes % 60)
    return remainingMinutes > 0 ? `${hours}h ${remainingMinutes}m` : `${hours}h`
  }
}
</script>