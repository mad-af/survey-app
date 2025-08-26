<template>
  <div v-if="isOpen" class="drawer drawer-end">
    <input id="user-drawer-toggle" type="checkbox" class="drawer-toggle" :checked="isOpen" />
    <div class="drawer-content">
      <!-- This is where the main content would be, but we're using this as a component -->
    </div>
    <div class="drawer-side">
      <label for="user-drawer-toggle" aria-label="close sidebar" class="drawer-overlay z-40" @click="closeDrawer"></label>
      <div class="bg-base-200 text-base-content min-h-full w-80 p-4 relative z-50 overflow-y-auto">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold">{{ title }}</h3>
          <button 
            class="btn btn-sm btn-circle btn-ghost" 
            @click="closeDrawer"
          >
            <X :size="16" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Name Field -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Name *</span>
            </label>
            <input 
              type="text" 
              v-model="form.name"
              placeholder="Enter full name" 
              class="input input-bordered w-full"
              :class="{ 'input-error': errors.name }"
              required
            />
            <label v-if="errors.name" class="label">
              <span class="label-text-alt text-error">{{ errors.name }}</span>
            </label>
          </div>

          <!-- Email Field -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Email *</span>
            </label>
            <input 
              type="email" 
              v-model="form.email"
              placeholder="Enter email address" 
              class="input input-bordered w-full"
              :class="{ 'input-error': errors.email }"
              required
            />
            <label v-if="errors.email" class="label">
              <span class="label-text-alt text-error">{{ errors.email }}</span>
            </label>
          </div>

          <!-- Password Field -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Password *</span>
            </label>
            <div class="relative">
              <input 
                :type="showPassword ? 'text' : 'password'"
                v-model="form.password"
                placeholder="Enter password" 
                class="input input-bordered w-full pr-10"
                :class="{ 'input-error': errors.password }"
                required
              />
              <button 
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                @click="showPassword = !showPassword"
              >
                <Eye v-if="!showPassword" :size="16" class="text-gray-400" />
                <EyeOff v-else :size="16" class="text-gray-400" />
              </button>
            </div>
            <label v-if="errors.password" class="label">
              <span class="label-text-alt text-error">{{ errors.password }}</span>
            </label>
          </div>

          <!-- Confirm Password Field -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Confirm Password *</span>
            </label>
            <div class="relative">
              <input 
                :type="showConfirmPassword ? 'text' : 'password'"
                v-model="form.password_confirmation"
                placeholder="Confirm password" 
                class="input input-bordered w-full pr-10"
                :class="{ 'input-error': errors.password_confirmation }"
                required
              />
              <button 
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <Eye v-if="!showConfirmPassword" :size="16" class="text-gray-400" />
                <EyeOff v-else :size="16" class="text-gray-400" />
              </button>
            </div>
            <label v-if="errors.password_confirmation" class="label">
              <span class="label-text-alt text-error">{{ errors.password_confirmation }}</span>
            </label>
          </div>

          <!-- Role Field -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Role *</span>
            </label>
            <select 
              v-model="form.role"
              class="select select-bordered w-full"
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
          <div class="flex gap-2 pt-4">
            <button 
              type="button" 
              class="btn btn-outline flex-1"
              @click="closeDrawer"
              :disabled="loading"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary flex-1"
              :disabled="loading"
            >
              <span v-if="loading" class="loading loading-spinner loading-sm"></span>
              {{ loading ? 'Creating...' : 'Create User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { X, Eye, EyeOff } from 'lucide-vue-next'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Add New User'
  }
})

// Emits
const emit = defineEmits(['close', 'submit'])

// Reactive data
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: ''
})

const errors = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}



const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
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
  
  // Password validation
  if (!form.password) {
    errors.password = 'Password is required'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
    isValid = false
  }
  
  // Password confirmation validation
  if (!form.password_confirmation) {
    errors.password_confirmation = 'Please confirm your password'
    isValid = false
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match'
    isValid = false
  }
  
  // Role validation
  if (!form.role) {
    errors.role = 'Role is required'
    isValid = false
  }
  
  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Emit the form data to parent component
    emit('submit', { ...form })
    
    // Reset form after successful submission
    resetForm()
  } catch (error) {
    console.error('Error creating user:', error)
  } finally {
    loading.value = false
  }
}

// Watch for drawer close to reset form
watch(() => props.isOpen, (newValue) => {
  if (!newValue) {
    resetForm()
    showPassword.value = false
    showConfirmPassword.value = false
  }
})
</script>