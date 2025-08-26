<template>
  <div class="breadcrumbs text-sm px-4 border-b border-base-300">
    <ul>
      <li v-for="(item, index) in breadcrumbItems" :key="index">
        <a 
          v-if="item.href && index < breadcrumbItems.length - 1" 
          :href="item.href" 
          class="group flex items-center gap-2 text-base-content/70 hover:text-primary transition-colors"
        >
          <Home 
            v-if="index === 0" 
            :size="16" 
            class="text-base-content/60 group-hover:text-primary transition-colors"
          />
          {{ item.label }}
        </a>
        
        <span 
          v-else 
          :class="[
            'flex items-center gap-2',
            index === breadcrumbItems.length - 1 
              ? 'text-base-content' 
              : 'text-base-content/70'
          ]"
        >
          <Home 
            v-if="index === 0" 
            :size="16" 
            class="text-base-content/60"
          />
          {{ item.label }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Home } from 'lucide-vue-next'

// Props
const props = defineProps({
  items: {
    type: Array,
    default: () => [
      { label: 'Dashboard', href: '/dashboard' },
      { label: 'Current Page' }
    ]
  }
})

// Computed
const breadcrumbItems = computed(() => {
  return props.items
})
</script>