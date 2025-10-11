<template>
  <dialog :id="modalId" class="modal">
    <div class="modal-box">
      <!-- Close button at corner -->
      <form method="dialog">
        <button 
          class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
          @click="handleCancel"
        >
          ✕
        </button>
      </form>
      
      <!-- Modal Header -->
      <h3 class="text-lg font-bold mb-4">
        {{ title }}
      </h3>
      
      <!-- Modal Content -->
      <div class="py-4">
        <p class="text-base">{{ message }}</p>
        
        <!-- Additional content slot -->
        <slot name="content"></slot>
      </div>
      
      <!-- Modal Actions -->
      <div class="modal-action">
        <form method="dialog" class="flex gap-2">
          <!-- Cancel Button -->
          <button 
            type="button"
            :class="cancelButtonClass"
            @click="handleCancel"
          >
            {{ cancelText }}
          </button>
          
          <!-- Confirm Button -->
          <button 
            type="button"
            :class="confirmButtonClass"
            @click="handleConfirm"
          >
            {{ confirmText }}
          </button>
        </form>
      </div>
    </div>
    
    <!-- Modal backdrop - closes when clicked outside -->
    <form method="dialog" class="modal-backdrop">
      <button @click="handleCancel">close</button>
    </form>
  </dialog>
</template>

<script setup>
import { computed, inject } from 'vue'

// Props
const props = defineProps({
  // Modal identification
  modalId: {
    type: String,
    required: true
  },
  
  // Content props
  title: {
    type: String,
    default: 'Konfirmasi'
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin melanjutkan?'
  },
  
  // Button text props
  confirmText: {
    type: String,
    default: 'Ya'
  },
  cancelText: {
    type: String,
    default: 'Batal'
  },
  
  // Button styling props
  confirmButtonType: {
    type: String,
    default: 'primary', // primary, secondary, accent, success, warning, error
    validator: (value) => ['primary', 'secondary', 'accent', 'success', 'warning', 'error'].includes(value)
  },
  cancelButtonType: {
    type: String,
    default: 'ghost',
    validator: (value) => ['primary', 'secondary', 'accent', 'success', 'warning', 'error', 'ghost', 'outline'].includes(value)
  },
  
  // Loading state
  loading: {
    type: Boolean,
    default: false
  },
  
  // Disable confirm button
  disabled: {
    type: Boolean,
    default: false
  },
  
  // Toast control props
  // Show success toast immediately on confirm click
  successOnConfirm: {
    type: Boolean,
    default: true
  },
  successMessage: {
    type: String,
    default: 'Aksi berhasil'
  }
})

// Emits
const emit = defineEmits(['confirm', 'cancel', 'close'])

// Toast notification
const showToast = inject('showToast', () => { })

// NOTE: Toast control props are merged into the main props above

// Computed classes for buttons
const confirmButtonClass = computed(() => {
  let classes = ['btn']
  
  if (props.confirmButtonType !== 'ghost' && props.confirmButtonType !== 'outline') {
    classes.push(`btn-${props.confirmButtonType}`)
  } else {
    classes.push(`btn-${props.confirmButtonType}`)
  }
  
  if (props.loading) {
    classes.push('loading')
  }
  
  if (props.disabled) {
    classes.push('btn-disabled')
  }
  
  return classes.join(' ')
})

const cancelButtonClass = computed(() => {
  let classes = ['btn']
  
  if (props.cancelButtonType !== 'ghost' && props.cancelButtonType !== 'outline') {
    classes.push(`btn-${props.cancelButtonType}`)
  } else {
    classes.push(`btn-${props.cancelButtonType}`)
  }
  
  return classes.join(' ')
})

// Methods
const openModal = () => {
  const modal = document.getElementById(props.modalId)
  if (modal) {
    modal.showModal()
  }
}

const closeModal = () => {
  const modal = document.getElementById(props.modalId)
  if (modal) {
    modal.close()
  }
}

const handleConfirm = () => {
  if (!props.disabled && !props.loading) {
    emit('confirm')
    // Show success toast (configurable)
    if (props.successOnConfirm) {
      showToast(props.successMessage, 'success')
    }
    closeModal()
  }
}

const handleCancel = () => {
  emit('cancel')
  emit('close')
  closeModal()
}

// Expose methods to parent component
defineExpose({
  openModal,
  closeModal
})
</script>

<style scoped>
/* Additional custom styles if needed */
.modal-box {
  max-width: 32rem;
}
</style>