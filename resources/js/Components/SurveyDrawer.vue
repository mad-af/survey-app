<template>
  <div v-if="isOpen" class="drawer drawer-end">
    <input id="survey-drawer-toggle" type="checkbox" class="drawer-toggle" :checked="isOpen" />
    <div class="drawer-content">
      <!-- This is where the main content would be, but we're using this as a component -->
    </div>
    <div class="drawer-side">
      <label for="survey-drawer-toggle" aria-label="close sidebar" class="drawer-overlay z-40" @click="closeDrawer"></label>
      <div class="bg-base-200 text-base-content min-h-full w-80 p-3 relative z-50 overflow-visible">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between mb-4">
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
          <!-- Title Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Title</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <input 
              type="text" 
              v-model="form.title"
              placeholder="Enter survey title" 
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': errors.title }"
              required
            />
            <label v-if="errors.title" class="label">
              <span class="label-text-alt text-error">{{ errors.title }}</span>
            </label>
          </div>

          <!-- Description Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Description</span>
            </label>
            <textarea 
              v-model="form.description"
              placeholder="Enter survey description" 
              class="textarea textarea-bordered textarea-sm w-full h-20 resize-none"
              :class="{ 'textarea-error': errors.description }"
            ></textarea>
            <label v-if="errors.description" class="label">
              <span class="label-text-alt text-error">{{ errors.description }}</span>
            </label>
          </div>

          <!-- Status Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Status</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <select 
              v-model="form.status"
              class="select select-bordered select-sm w-full"
              :class="{ 'select-error': errors.status }"
              required
            >
              <option value="">Select status</option>
              <option value="draft">Draft</option>
              <option value="active">Active</option>
              <option value="closed">Closed</option>
            </select>
            <label v-if="errors.status" class="label">
              <span class="label-text-alt text-error">{{ errors.status }}</span>
            </label>
          </div>

          <!-- Visibility Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Visibility</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <select 
              v-model="form.visibility"
              class="select select-bordered select-sm w-full"
              :class="{ 'select-error': errors.visibility }"
              required
            >
              <option value="">Select visibility</option>
              <option value="private">Private</option>
              <option value="link">Link</option>
              <option value="public">Public</option>
            </select>
            <label v-if="errors.visibility" class="label">
              <span class="label-text-alt text-error">{{ errors.visibility }}</span>
            </label>
          </div>

          <!-- Start Date Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Start Date</span>
            </label>
            <input 
              type="datetime-local" 
              v-model="form.start_date"
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': errors.start_date }"
            />
            <label v-if="errors.start_date" class="label">
              <span class="label-text-alt text-error">{{ errors.start_date }}</span>
            </label>
          </div>

          <!-- End Date Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">End Date</span>
            </label>
            <input 
              type="datetime-local" 
              v-model="form.end_date"
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': errors.end_date }"
            />
            <label v-if="errors.end_date" class="label">
              <span class="label-text-alt text-error">{{ errors.end_date }}</span>
            </label>
          </div>

          <!-- Form Actions -->
          <div class="flex gap-2 pt-3">
            <button 
              type="button" 
              class="btn btn-sm flex-1"
              @click="closeDrawer"
              :disabled="loading"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="btn btn-primary btn-sm flex-1"
              :disabled="loading"
            >
              <span v-if="loading" class="loading loading-spinner loading-xs"></span>
              {{ loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Survey' : 'Create Survey') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, inject } from 'vue'
import axios from 'axios'
import { X } from 'lucide-vue-next'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Add New Survey'
  },
  surveyData: {
    type: Object,
    default: null
  },
  isEditMode: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close', 'submit', 'success'])

// Toast notification
const showToast = inject('showToast', () => {})

// Reactive data
const loading = ref(false)

const form = reactive({
  title: '',
  description: '',
  status: 'draft',
  visibility: 'public',
  start_date: '',
  end_date: ''
})

const errors = reactive({
  title: '',
  description: '',
  status: '',
  visibility: '',
  start_date: '',
  end_date: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}

const resetForm = () => {
  form.title = ''
  form.description = ''
  form.status = 'draft'
  form.visibility = 'private'
  form.start_date = ''
  form.end_date = ''
  
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
  
  // Title validation
  if (!form.title.trim()) {
    errors.title = 'Title is required'
    isValid = false
  }
  
  // Status validation
  if (!form.status) {
    errors.status = 'Status is required'
    isValid = false
  }
  
  // Visibility validation
  if (!form.visibility) {
    errors.visibility = 'Visibility is required'
    isValid = false
  }
  
  // Date validation
  if (form.start_date && form.end_date) {
    const startDate = new Date(form.start_date)
    const endDate = new Date(form.end_date)
    
    if (startDate >= endDate) {
      errors.end_date = 'End date must be after start date'
      isValid = false
    }
  }
  
  return isValid
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Prepare data for API submission
    const submitData = {
      title: form.title,
      description: form.description,
      status: form.status,
      visibility: form.visibility,
      start_date: form.start_date || null,
      end_date: form.end_date || null
    }
    
    let response
    
    if (props.isEditMode && props.surveyData) {
      // Update existing survey
      response = await axios.put(`/api/surveys/${props.surveyData.id}`, submitData)
    } else {
      // Create new survey
      response = await axios.post('/api/surveys', submitData)
    }
    
    if (response.data.success) {
      showToast(response.data.message, 'success')
      emit('success', response.data.data)
      emit('close')
      resetForm()
    } else {
      throw new Error(response.data.message || 'Failed to save survey')
    }
  } catch (error) {
    console.error('Error submitting survey:', error)
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = validationErrors[key][0]
        }
      })
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to save survey'
      showToast(errorMessage, 'error')
    }
  } finally {
    loading.value = false
  }
}

// Helper function to format datetime for input
const formatDateTimeForInput = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return ''
  
  // Format to YYYY-MM-DDTHH:MM for datetime-local input
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// Watch for drawer open/close and surveyData changes
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.isEditMode && props.surveyData) {
    // Fill form with survey data for editing
    form.title = props.surveyData.title || ''
    form.description = props.surveyData.description || ''
    form.status = props.surveyData.status || 'draft'
    form.visibility = props.surveyData.visibility || 'private'
    form.start_date = formatDateTimeForInput(props.surveyData.starts_at)
    form.end_date = formatDateTimeForInput(props.surveyData.ends_at)
  } else if (!newValue) {
    resetForm()
  }
})

watch(() => props.surveyData, (newValue) => {
  if (props.isOpen && props.isEditMode && newValue) {
    // Fill form with survey data when surveyData changes
    form.title = newValue.title || ''
    form.description = newValue.description || ''
    form.status = newValue.status || 'draft'
    form.visibility = newValue.visibility || 'private'
    form.start_date = formatDateTimeForInput(newValue.starts_at)
    form.end_date = formatDateTimeForInput(newValue.ends_at)
  }
})
</script>