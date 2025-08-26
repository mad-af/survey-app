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
        />

        <!-- Main Content -->
        <main class="flex-1 p-4">
          <slot />
        </main>

        <!-- Global Toast Notification -->
        <Toast 
          :message="toastMessage" 
          :type="toastType" 
          :show="isToastVisible" 
          @close="closeToast"
        />
      </div>

      <!-- Sidebar -->
      <div class="drawer-side">
        <label for="drawer-toggle" aria-label="close sidebar" class="drawer-overlay"></label>
        <Sidebar :currentRoute="currentRoute" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, provide } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Header from '@/Components/Header.vue'
import Sidebar from '@/Components/Sidebar.vue'
import Toast from '@/Components/Toast.vue'

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
  }
})

// Get current route
const page = usePage()
const currentRoute = computed(() => {
  return page.component.value || 'dashboard'
})

// Toast functionality
const isToastVisible = ref(false)
const toastMessage = ref('')
const toastType = ref('info')

const showToastNotification = (message, type = 'info') => {
  toastMessage.value = message
  toastType.value = type
  isToastVisible.value = true
}

const closeToast = () => {
  isToastVisible.value = false
  toastMessage.value = ''
}

// Provide toast functions to child components
provide('showToast', showToastNotification)
</script>