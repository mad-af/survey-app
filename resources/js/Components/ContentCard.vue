<template>
  <div class="w-full mx-auto" :class="maxWidth">
    <!-- Content Card -->
    <div class="card bg-base-100 shadow-xl border border-base-300">
      <div class="card-body p-6 lg:p-8">
        
        <!-- Page Title -->
        <div v-if="title" class="flex items-center gap-3 mb-6">
          <component :is="titleIcon" v-if="titleIcon" class="w-8 h-8 text-primary" />
          <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-base-content">{{ title }}</h1>
            <p v-if="subtitle" class="text-base-content/70 mt-1">{{ subtitle }}</p>
          </div>
        </div>
        
        <!-- Slot untuk konten utama -->
        <div class="space-y-6">
          <slot />
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
// Props
const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  titleIcon: {
    type: [String, Object],
    default: null
  },
  maxWidth: {
    type: String,
    default: 'max-w-4xl'
  }
})
</script>