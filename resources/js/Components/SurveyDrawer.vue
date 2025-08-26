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
          <div class="form-control relative z-[9999]">
            <label class="label text-sm">
              <span class="label-text">Status</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <select 
              v-model="form.status"
              class="select select-bordered select-sm w-full relative z-[9999]"
              :class="{ 'select-error': errors.status }"
              required
              style="z-index: 9999 !important;"
            >
              <option value="">Select status</option>
              <option value="draft">Draft</option>
              <option value="active">Active</option>
              <option value="paused">Paused</option>
              <option value="completed">Completed</option>
              <option value="archived">Archived</option>
            </select>
            <label v-if="errors.status" class="label">
              <span class="label-text-alt text-error">{{ errors.status }}</span>
            </label>
          </div>

          <!-- Visibility Field -->
          <div class="form-control relative z-[9998]">
            <label class="label text-sm">
              <span class="label-text">Visibility</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <select 
              v-model="form.visibility"
              class="select select-bordered select-sm w-full relative z-[9998]"
              :class="{ 'select-error': errors.visibility }"
              required
              style="z-index: 9998 !important;"
            >
              <option value="">Select visibility</option>
              <option value="public">Public</option>
              <option value="private">Private</option>
              <option value="restricted">Restricted</option>
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
import { ref, reactive, watch } from 'vue'
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
const emit = defineEmits(['close', 'submit'])

// Reactive data
const loading = ref(false)

const form = reactive({
  title: '',
  description: '',
  status: 'draft',
  visibility: 'private',
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
    // Prepare data for submission
    const submitData = { ...form }
    
    // For edit mode, include survey ID
    if (props.isEditMode && props.surveyData) {
      submitData.id = props.surveyData.id
    }
    
    // Emit the form data to parent component
    emit('submit', submitData)
    
    // Reset form after successful submission
    resetForm()
  } catch (error) {
    console.error('Error submitting survey:', error)
  } finally {
    loading.value = false
  }
}

// Watch for drawer open/close and surveyData changes
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.isEditMode && props.surveyData) {
    // Fill form with survey data for editing
    form.title = props.surveyData.title || ''
    form.description = props.surveyData.description || ''
    form.status = props.surveyData.status || 'draft'
    form.visibility = props.surveyData.visibility || 'private'
    form.start_date = props.surveyData.start_date || ''
    form.end_date = props.surveyData.end_date || ''
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
    form.start_date = newValue.start_date || ''
    form.end_date = newValue.end_date || ''
  }
})
</script>