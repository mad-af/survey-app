<template>
  <div class="min-h-screen bg-base-200">
    <!-- Sidebar -->
    <div class="drawer lg:drawer-open">
      <input id="drawer-toggle" type="checkbox" class="drawer-toggle" />
      <div class="drawer-content flex flex-col">
        <!-- Header -->
        <Header 
          :userName="userName" 
          :userRole="userRole" 
          :profileImage="profileImage" 
          :pageTitle="pageTitle"
          :breadcrumbItems="breadcrumbItems"
        />

        <!-- Main Content -->
        <main class="flex-1 p-4">
          <slot />
        </main>
      </div>

      <!-- Sidebar -->
      <Sidebar :currentRoute="currentRoute" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'
import Sidebar from '@/Components/Sidebar.vue'

// Props
const props = defineProps({
  userName: {
    type: String,
    default: 'Esther H.'
  },
  userRole: {
    type: String,
    default: 'Designer'
  },
  profileImage: {
    type: String,
    default: null
  },
  pageTitle: {
    type: String,
    default: 'My Dashboard'
  },
  breadcrumbItems: {
    type: Array,
    default: () => [
      { label: 'Home', href: '/dashboard' },
    ]
  }
})

// Get current route
const page = usePage()
const currentRoute = computed(() => {
  return page.component.value || 'dashboard'
})
</script>