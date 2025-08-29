<template>
  <div class="min-h-screen bg-base-200">

    <!-- Main Content Area -->
    <main class="p-4 pt-8 mx-auto space-y-3 max-w-4xl md:pt-32 lg:p-8">

      <!-- Custom Slot Content -->
      <slot />
    </main>

    <!-- Theme Selector Component -->
    <ThemeSelector />

    <!-- Global Toast Notification -->
    <Toast :message="toastMessage" :type="toastType" :show="isToastVisible" @close="closeToast" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ThemeSelector from '@/Components/ThemeSelector.vue'
import Toast from '@/Components/Toast.vue'

// Toast functionality
const isToastVisible = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const showToast = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  isToastVisible.value = true
}

const closeToast = () => {
  isToastVisible.value = false
}

// Expose methods to parent components
defineExpose({
  showToast,
  closeToast
})
</script>
