<template>
  <div class="breadcrumbs text-sm px-4 border-b border-base-300">
    <ul>
      <li v-for="(item, index) in processedBreadcrumbs" :key="index">
        <Link 
          v-if="item.href && index < processedBreadcrumbs.length - 1" 
          :href="item.href" 
          class="group flex items-center gap-2 text-base-content/70 hover:text-primary transition-colors"
        >
          <Home 
            v-if="index === 0" 
            :size="16" 
            class="text-base-content/60 group-hover:text-primary transition-colors"
          />
          {{ item.label }}
        </Link>
        
        <span 
          v-else 
          :class="[
            'flex items-center gap-2',
            index === processedBreadcrumbs.length - 1 
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
import { Link, usePage } from '@inertiajs/vue3'
import { Home } from 'lucide-vue-next'

// Props
const props = defineProps({
  items: {
    type: Array,
    default: null // Allow null to trigger auto-generation
  },
  autoGenerate: {
    type: Boolean,
    default: true // Enable auto-generation by default
  },
  customLabels: {
    type: Object,
    default: () => ({})
  }
})

// Get current page info
const page = usePage()

// Route to label mapping
const routeLabels = {
  '/dashboard': 'Dashboard',
  '/dashboard/user-management': 'User Management',
  '/dashboard/surveys': 'Surveys',
  '/dashboard/reports': 'Reports',
  '/dashboard/settings': 'Settings',
  '/dashboard/profile': 'Profile',
  // Add more routes as needed
  ...props.customLabels
}

// Helper function to generate breadcrumbs from URL
const generateBreadcrumbsFromUrl = (url) => {
  const segments = url.split('/').filter(segment => segment !== '')
  const breadcrumbs = []
  
  // Always start with Dashboard
  breadcrumbs.push({
    label: 'Dashboard',
    href: '/dashboard'
  })
  
  // Build path progressively
  let currentPath = ''
  
  segments.forEach((segment, index) => {
    currentPath += `/${segment}`
    
    // Skip the first 'dashboard' segment as it's already added
    if (segment === 'dashboard') return
    
    // Get label from mapping or format segment
    const label = routeLabels[currentPath] || 
                 segment.split('-')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ')
    
    // Only add href if it's not the last segment
    const isLast = index === segments.length - 1
    
    breadcrumbs.push({
      label,
      href: isLast ? null : currentPath
    })
  })
  
  return breadcrumbs
}

// Computed
const processedBreadcrumbs = computed(() => {
  const currentUrl = page.url
  
  // Use provided items or auto-generate from URL
  let items = props.items
  
  if (!items && props.autoGenerate) {
    items = generateBreadcrumbsFromUrl(currentUrl)
  } else if (!items) {
    // Fallback to default
    items = [
      { label: 'Dashboard', href: '/dashboard' },
      { label: 'Current Page' }
    ]
  }
  
  return items.map((item, index) => {
    // Check if this item is the current active page
    const isActive = item.href && currentUrl === item.href
    
    // If it's the last item and no href, it's also active
    const isLastWithoutHref = index === items.length - 1 && !item.href
    
    return {
      ...item,
      isActive: isActive || isLastWithoutHref
    }
  })
})
</script>