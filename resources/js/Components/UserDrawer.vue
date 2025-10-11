<template>
  <div v-if="isOpen" class="drawer drawer-end">
    <input id="user-drawer-toggle" type="checkbox" class="drawer-toggle" :checked="isOpen" />
    <div class="drawer-content">
      <!-- This is where the main content would be, but we're using this as a component -->
    </div>
    <div class="drawer-side">
      <label for="user-drawer-toggle" aria-label="close sidebar" class="z-40 drawer-overlay" @click="closeDrawer"></label>
      <div class="overflow-y-auto relative z-50 p-3 w-72 min-h-full bg-base-200 text-base-content">
        <!-- Drawer Header -->
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-base font-semibold">{{ title }}</h3>
          <button 
            class="btn btn-xs btn-circle btn-ghost" 
            @click="closeDrawer"
          >
            <X :size="14" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-3">
          <!-- Name Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Name</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <input 
              type="text" 
              v-model="form.name"
              placeholder="Enter full name" 
              class="w-full input input-bordered input-sm"
              :class="{ 'input-error': errors.name }"
              required
            />
            <label v-if="errors.name" class="label">
              <span class="label-text-alt text-error">{{ errors.name }}</span>
            </label>
          </div>

          <!-- Email Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Email</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <input 
              type="email" 
              v-model="form.email"
              placeholder="Enter email address" 
              class="w-full input input-bordered input-sm"
              :class="{ 'input-error': errors.email }"
              required
            />
            <label v-if="errors.email" class="label">
              <span class="label-text-alt text-error">{{ errors.email }}</span>
            </label>
          </div>



          <!-- Role Field -->
          <div class="form-control relative z-[60]">
            <label class="text-sm label">
              <span class="label-text">Role</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <select 
              v-model="form.role"
              class="select select-bordered select-sm w-full relative z-[60]"
              :class="{ 'select-error': errors.role }"
              required
            >
              <option value="">Select a role</option>
              <option value="admin">Admin</option>
              <option value="surveyor">Surveyor</option>
            </select>
            <label v-if="errors.role" class="label">
              <span class="label-text-alt text-error">{{ errors.role }}</span>
            </label>
          </div>

          <!-- Form Actions -->
          <div class="flex gap-2 pt-3">
            <button 
              type="button" 
              class="flex-1 btn btn-sm"
              @click="closeDrawer"
              :disabled="loading"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="flex-1 btn btn-primary btn-sm"
              :disabled="loading"
            >
              <span v-if="loading" class="loading loading-spinner loading-xs"></span>
              {{ loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update User' : 'Create User') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, inject } from 'vue'
import { X } from 'lucide-vue-next'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Add New User'
  },
  userData: {
    type: Object,
    default: null
  },
  isEditMode: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close', 'submit'])

// Toast notification
const showToast = inject('showToast', () => { console.warn('showToast injection not available') })

// Reactive data
const loading = ref(false)

const form = reactive({
  name: '',
  email: '',
  role: ''
})

const errors = reactive({
  name: '',
  email: '',
  role: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}



const resetForm = () => {
  form.name = ''
  form.email = ''
  form.role = ''
  
  // Clear errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const validateForm = () => {
  let isValid = true
  
  // Clear previous errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  // Name validation
  if (!form.name.trim()) {
    errors.name = 'Name is required'
    isValid = false
  }
  
  // Email validation
  if (!form.email.trim()) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }
  

  
  // Role validation
  if (!form.role) {
    errors.role = 'Role is required'
    isValid = false
  }
  
  // Show toast error if validation fails
  if (!isValid) {
    showToast('Mohon perbaiki input yang tidak valid', 'error')
  }
  
  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Prepare data for submission
    const submitData = { ...form }
    
    // For edit mode, include user ID
    if (props.isEditMode && props.userData) {
      submitData.id = props.userData.id
    }
    
    showToast('Data pengguna berhasil disimpan', 'success')
    // Emit the form data to parent component
    emit('submit', submitData)
    
    // Reset form after successful submission
    resetForm()
  } catch (error) {
    console.error('Error submitting user:', error)
    const errorMessage = error?.response?.data?.message || error?.message || 'Gagal mengirim data pengguna'
    showToast(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

// Watch for drawer open/close and userData changes
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.isEditMode && props.userData) {
    // Fill form with user data for editing
    form.name = props.userData.name || ''
    form.email = props.userData.email || ''
    form.role = props.userData.role || ''
  } else if (!newValue) {
    resetForm()
  }
})

watch(() => props.userData, (newValue) => {
  if (props.isOpen && props.isEditMode && newValue) {
    // Fill form with user data when userData changes
    form.name = newValue.name || ''
    form.email = newValue.email || ''
    form.role = newValue.role || ''
  }
})
</script>