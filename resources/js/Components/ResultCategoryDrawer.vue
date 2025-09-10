<template>
  <div v-if="isOpen" class="drawer drawer-end">
    <input id="result-category-drawer-toggle" type="checkbox" class="drawer-toggle" :checked="isOpen" />
    <div class="drawer-content">
      <!-- This is where the main content would be, but we're using this as a component -->
    </div>
    <div class="drawer-side">
      <label for="result-category-drawer-toggle" aria-label="close sidebar" class="z-40 drawer-overlay" @click="closeDrawer"></label>
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
          <!-- Name Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Category Name</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <input 
              type="text" 
              v-model="form.name"
              placeholder="Enter category name" 
              class="w-full input input-bordered input-sm"
              :class="{ 'input-error': errors.name }"
              required
            />
            <label v-if="errors.name" class="label">
              <span class="label-text-alt text-error">{{ errors.name }}</span>
            </label>
          </div>

          <!-- Description Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Description</span>
            </label>
            <textarea 
              v-model="form.description"
              placeholder="Enter category description (optional)" 
              class="w-full h-20 resize-none textarea textarea-bordered textarea-sm"
              :class="{ 'textarea-error': errors.description }"
            ></textarea>
            <label v-if="errors.description" class="label">
              <span class="label-text-alt text-error">{{ errors.description }}</span>
            </label>
          </div>

          <!-- Color Field -->
          <div class="form-control">
            <label class="text-sm label">
              <span class="label-text">Color</span>
            </label>
            <select 
              v-model="form.color"
              class="w-full select select-bordered select-sm"
              :class="{ 'select-error': errors.color }"
            >
              <option value="">Select color</option>
              <option value="primary">Primary</option>
              <option value="secondary">Secondary</option>
              <option value="accent">Accent</option>
              <option value="success">Success</option>
              <option value="warning">Warning</option>
              <option value="error">Error</option>
              <option value="info">Info</option>
            </select>
            <label v-if="errors.color" class="label">
              <span class="label-text-alt text-error">{{ errors.color }}</span>
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
              {{ loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Category' : 'Create Category') }}
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
    default: 'Result Category'
  },
  categoryData: {
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
  name: '',
  description: '',
  color: '',
  survey_id: null
})

const errors = reactive({
  name: '',
  description: '',
  color: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}

const resetForm = () => {
  form.name = ''
  form.description = ''
  form.color = ''
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
  
  // Name validation
  if (!form.name.trim()) {
    errors.name = 'Category name is required'
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
      name: form.name,
      description: form.description,
      color: form.color,
      survey_id: form.survey_id
    }
    
    let response
    
    if (props.isEditMode && props.categoryData) {
      // Update existing category
      response = await axios.put(`/api/surveys/${form.survey_id}/result-categories/${props.categoryData.id}`, submitData)
    } else {
      // Create new category
      response = await axios.post(`/api/surveys/${form.survey_id}/result-categories`, submitData)
    }
    
    if (response.data.success) {
      showToast(response.data.message, 'success')
      emit('success', response.data.data)
      emit('close')
      resetForm()
    } else {
      throw new Error(response.data.message || 'Failed to save result category')
    }
  } catch (error) {
    console.error('Error submitting result category:', error)
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = validationErrors[key][0]
        }
      })
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to save result category'
      showToast(errorMessage, 'error')
    }
  } finally {
    loading.value = false
  }
}

// Watch for drawer open/close and categoryData changes
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.isEditMode && props.categoryData) {
    // Fill form with category data for editing
    form.name = props.categoryData.name || ''
    form.description = props.categoryData.description || ''
    form.color = props.categoryData.color || ''
    form.survey_id = props.categoryData.survey_id || props.surveyId
  } else if (!newValue) {
    resetForm()
  }
})

watch(() => props.categoryData, (newValue) => {
  if (props.isOpen && props.isEditMode && newValue) {
    // Fill form with category data when categoryData changes
    form.name = newValue.name || ''
    form.description = newValue.description || ''
    form.color = newValue.color || ''
    form.survey_id = newValue.survey_id || props.surveyId
  }
})

// Watch for surveyId changes
watch(() => props.surveyId, (newSurveyId) => {
  form.survey_id = newSurveyId
}, { immediate: true })
</script>