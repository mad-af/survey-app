<template>
  <div class="shadow-sm card card-sm bg-base-100">
    <div class="p-4 card-body">
      <div class="card-header">
        <h3 class="text-lg font-semibold">Survey Statistics</h3>
      </div>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg stat bg-base-200">
          <div class="stat-title">Total Responses</div>
          <div class="stat-value text-primary">{{ totalResponses || 0 }}</div>
          <div class="stat-desc">All survey responses</div>
        </div>
        <div class="rounded-lg stat bg-base-200">
          <div class="stat-title">Completed</div>
          <div class="stat-value text-success">{{ completedResponses || 0 }}</div>
          <div class="stat-desc">{{ completionRate || 0 }}% completion rate</div>
        </div>
        <div class="rounded-lg stat bg-base-200">
          <div class="stat-title">In Progress</div>
          <div class="stat-value text-warning">{{ (inProgressResponses || 0) + (startedResponses || 0) }}</div>
          <div class="stat-desc">Currently being filled</div>
        </div>
        <div class="rounded-lg stat bg-base-200">
          <div class="stat-title">Abandoned</div>
          <div class="stat-value text-error">{{ abandonedResponses || 0 }}</div>
          <div class="stat-desc">Left incomplete</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
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
  startedResponses: {
    type: Number,
    default: 0
  },
  abandonedResponses: {
    type: Number,
    default: 0
  },
  completionRate: {
    type: Number,
    default: 0
  }
})
</script>