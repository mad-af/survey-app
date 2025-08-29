<template>
  <div class="min-h-screen bg-base-200">

    <!-- Main Content Area -->
    <main class="flex flex-1 justify-center items-center p-4 lg:p-8">
      <slot />
    </main>

    <!-- Theme Selector Component -->
    <ThemeSelector />

    <!-- Global Toast Notification -->
    <Toast :message="toastMessage" :type="toastType" :show="isToastVisible" @close="closeToast" />

  </div>
</template>

<script setup>
import { ref, provide } from 'vue'
import ThemeSelector from '@/Components/ThemeSelector.vue'
import Toast from '@/Components/Toast.vue'

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
