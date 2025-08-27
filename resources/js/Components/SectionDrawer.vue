<template>
  <div v-if="isOpen" class="drawer drawer-end">
    <input id="survey-drawer-toggle" type="checkbox" class="drawer-toggle" :checked="isOpen" />
    <div class="drawer-content">
      <!-- This is where the main content would be, but we're using this as a component -->
    </div>
    <div class="drawer-side">
      <label for="survey-drawer-toggle" aria-label="close sidebar" class="z-40 drawer-overlay" @click="closeDrawer"></label>
      <div class="overflow-visible relative z-50 p-3 w-80 min-h-full bg-base-200 text-base-content">
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
          <!-- Title Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Section Title</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <input 
              type="text" 
              v-model="form.title"
              placeholder="Enter section title" 
              class="w-full input input-bordered input-sm"
              :class="{ 'input-error': errors.title }"
              required
            />
            <label v-if="errors.title" class="label">
              <span class="label-text-alt text-error">{{ errors.title }}</span>
            </label>
          </div>

          <!-- Description Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Description</span>
            </label>
            <textarea 
              v-model="form.description"
              placeholder="Enter section description (optional)" 
              class="w-full h-20 resize-none textarea textarea-bordered textarea-sm"
              :class="{ 'textarea-error': errors.description }"
            ></textarea>
            <label v-if="errors.description" class="label">
              <span class="label-text-alt text-error">{{ errors.description }}</span>
            </label>
          </div>

          <!-- Order Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Order</span>
            </label>
            <input 
              type="number" 
              v-model.number="form.order"
              placeholder="Leave empty for auto-order" 
              class="w-full input input-bordered input-sm"
              :class="{ 'input-error': errors.order }"
              min="1"
            />
            <label v-if="errors.order" class="label">
              <span class="label-text-alt text-error">{{ errors.order }}</span>
            </label>
          </div>

          <!-- Survey ID (Hidden field for API) -->
          <input type="hidden" v-model="form.survey_id" />

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
              {{ loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Section' : 'Create Section') }}
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
    default: 'Add New Section'
  },
  sectionData: {
    type: Object,
    default: () => ({})
  },
  isEditMode: {
    type: Boolean,
    default: false
  },
  surveyId: {
    type: [String, Number],
    required: true
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
  order: null,
  survey_id: null
})

const errors = reactive({
  title: '',
  description: '',
  order: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}

const resetForm = () => {
  form.title = ''
  form.description = ''
  form.order = null
  form.survey_id = props.surveyId
  
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
  
  // Order validation (only if provided)
  if (form.order !== null && form.order !== undefined && form.order < 1) {
    errors.order = 'Order must be a positive number'
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
    // Prepare data for API submission
    const submitData = {
      title: form.title,
      description: form.description,
      survey_id: form.survey_id
    }
    
    // Only include order if it's provided
    if (form.order !== null && form.order !== undefined) {
      submitData.order = form.order
    }
    
    let response
    
    if (props.isEditMode && props.sectionData) {
      // Update existing section
      response = await axios.put(`/api/surveys/${form.survey_id}/sections/${props.sectionData.id}`, submitData)
    } else {
      // Create new section
      response = await axios.post(`/api/surveys/${form.survey_id}/sections`, submitData)
    }
    
    if (response.data.success) {
      showToast(response.data.message, 'success')
      emit('success', response.data.data)
      emit('close')
      resetForm()
    } else {
      throw new Error(response.data.message || 'Failed to save section')
    }
  } catch (error) {
    console.error('Error submitting section:', error)
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = validationErrors[key][0]
        }
      })
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to save section'
      showToast(errorMessage, 'error')
    }
  } finally {
    loading.value = false
  }
}

// Watch for drawer open/close and sectionData changes
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.isEditMode && props.sectionData) {
    // Fill form with section data for editing
    form.title = props.sectionData.title || ''
    form.description = props.sectionData.description || ''
    form.order = props.sectionData.order || null
    form.survey_id = props.sectionData.survey_id || props.surveyId
  } else if (!newValue) {
    resetForm()
  }
})

watch(() => props.sectionData, (newValue) => {
  if (props.isOpen && props.isEditMode && newValue) {
    // Fill form with section data when sectionData changes
    form.title = newValue.title || ''
    form.description = newValue.description || ''
    form.order = newValue.order || null
    form.survey_id = newValue.survey_id || props.surveyId
  }
})

// Watch for surveyId changes
watch(() => props.surveyId, (newSurveyId) => {
  form.survey_id = newSurveyId
}, { immediate: true })
</script>