<template>
  <div 
    v-if="show" 
    class="toast toast-top toast-end z-50"
    :class="{
      'toast-success': type === 'success',
      'toast-error': type === 'error',
      'toast-warning': type === 'warning',
      'toast-info': type === 'info'
    }"
  >
    <div 
      class="alert"
      :class="{
        'alert-success': type === 'success',
        'alert-error': type === 'error',
        'alert-warning': type === 'warning',
        'alert-info': type === 'info'
      }"
    >
      <!-- Icon based on type -->
      <CheckCircle 
        v-if="type === 'success'" 
        class="stroke-current shrink-0 h-6 w-6" 
      />
      
      <XCircle 
        v-else-if="type === 'error'" 
        class="stroke-current shrink-0 h-6 w-6" 
      />
      
      <AlertTriangle 
        v-else-if="type === 'warning'" 
        class="stroke-current shrink-0 h-6 w-6" 
      />
      
      <Info 
        v-else-if="type === 'info'" 
        class="stroke-current shrink-0 h-6 w-6" 
      />
      
      <!-- Message -->
      <span>{{ message }}</span>
      
      <!-- Close button -->
      <button 
        class="btn btn-sm btn-ghost" 
        @click="closeToast"
      >
        ×
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { CheckCircle, XCircle, AlertTriangle, Info } from 'lucide-vue-next'

// Props
const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  duration: {
    type: Number,
    default: 5000 // 5 seconds
  },
  show: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close'])

// Reactive data
let timeoutId = null

// Methods
const closeToast = () => {
  if (timeoutId) {
    clearTimeout(timeoutId)
  }
  emit('close')
}

const startAutoClose = () => {
  if (props.duration > 0) {
    timeoutId = setTimeout(() => {
      closeToast()
    }, props.duration)
  }
}

// Watch for show prop changes
watch(() => props.show, (newValue) => {
  if (newValue) {
    startAutoClose()
  } else {
    if (timeoutId) {
      clearTimeout(timeoutId)
    }
  }
})

// Start auto close on mount if show is true
onMounted(() => {
  if (props.show) {
    startAutoClose()
  }
})
</script>

<style scoped>
.toast {
  position: fixed;
  z-index: 9999;
}
</style>