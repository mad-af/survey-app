<template>
  <div class="min-h-screen bg-base-200">

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center p-4 lg:p-8">
      <ContentCard 
        :title="title" 
        :subtitle="subtitle" 
        :title-icon="titleIcon"
        :max-width="maxWidth"
      >
        <slot />
      </ContentCard>
    </main>

    <!-- Theme Selector Component -->
    <ThemeSelector />
   
  </div>
</template>

<script setup>
import ThemeSelector from '@/Components/ThemeSelector.vue'
import ContentCard from '@/Components/ContentCard.vue'

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
