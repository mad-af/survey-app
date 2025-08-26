<template>
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-xl font-bold text-base-content">{{ title }}</h1>
      <p v-if="description" class="text-sm text-base-content/60 mt-1">{{ description }}</p>
    </div>
    <div v-if="$slots.action" class="flex gap-2">
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script setup>
// Props
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: null
  }
})
</script>