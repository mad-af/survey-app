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
          <!-- Question Text Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Question Text</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <textarea 
              v-model="form.text"
              placeholder="Enter question text" 
              class="textarea textarea-bordered textarea-sm w-full h-20 resize-none"
              :class="{ 'textarea-error': errors.text }"
              required
            ></textarea>
            <label v-if="errors.text" class="label">
              <span class="label-text-alt text-error">{{ errors.text }}</span>
            </label>
          </div>

          <!-- Question Type Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Question Type</span>
              <span class="label-text-alt text-error">*</span>
            </label>
            <select 
              v-model="form.type"
              class="select select-bordered select-sm w-full"
              :class="{ 'select-error': errors.type }"
              required
            >
              <option value="">Select question type</option>
              <option value="short_text">Short Text</option>
              <option value="long_text">Long Text</option>
              <option value="single_choice">Single Choice</option>
              <option value="multiple_choice">Multiple Choice</option>
              <option value="number">Number</option>
              <option value="date">Date</option>
            </select>
            <label v-if="errors.type" class="label">
              <span class="label-text-alt text-error">{{ errors.type }}</span>
            </label>
          </div>

          <!-- Order Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Order</span>
            </label>
            <input 
              type="number" 
              v-model.number="form.order"
              placeholder="Question order" 
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': errors.order }"
              min="1"
            />
            <label v-if="errors.order" class="label">
              <span class="label-text-alt text-error">{{ errors.order }}</span>
            </label>
          </div>

          <!-- Score Weight Field -->
          <div class="form-control">
            <label class="label text-sm">
              <span class="label-text">Score Weight</span>
            </label>
            <input 
              type="number" 
              v-model.number="form.score_weight"
              placeholder="Score weight (optional)" 
              class="input input-bordered input-sm w-full"
              :class="{ 'input-error': errors.score_weight }"
              step="0.01"
              min="0"
            />
            <label v-if="errors.score_weight" class="label">
              <span class="label-text-alt text-error">{{ errors.score_weight }}</span>
            </label>
          </div>

          <!-- Required Field -->
          <div class="form-control">
            <label class="label cursor-pointer">
              <span class="label-text">Required Question</span>
              <input 
                type="checkbox" 
                v-model="form.required"
                class="checkbox checkbox-primary checkbox-sm"
              />
            </label>
            <div class="text-xs text-gray-500 mt-1">
              Mark this question as required for survey completion
            </div>
          </div>

          <!-- Section ID (Hidden field for API) -->
          <input type="hidden" v-model="form.section_id" />

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
              {{ loading ? (isEditMode ? 'Updating...' : 'Creating...') : (isEditMode ? 'Update Question' : 'Create Question') }}
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
    default: 'Add New Question'
  },
  questionData: {
    type: Object,
    default: () => ({})
  },
  isEditMode: {
    type: Boolean,
    default: false
  },
  sectionId: {
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
  text: '',
  type: '',
  required: false,
  order: 1,
  score_weight: null,
  section_id: null
})

const errors = reactive({
  text: '',
  type: '',
  required: '',
  order: '',
  score_weight: '',
  section_id: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}

const resetForm = () => {
  form.text = ''
  form.type = ''
  form.required = false
  form.order = 1
  form.score_weight = null
  form.section_id = props.sectionId
  
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
  
  // Text validation
  if (!form.text.trim()) {
    errors.text = 'Question text is required'
    isValid = false
  }
  
  // Type validation
  if (!form.type) {
    errors.type = 'Question type is required'
    isValid = false
  }
  
  // Order validation
  if (!form.order || form.order < 1) {
    errors.order = 'Order must be a positive number'
    isValid = false
  }
  
  // Score weight validation (optional but must be positive if provided)
  if (form.score_weight !== null && form.score_weight < 0) {
    errors.score_weight = 'Score weight must be a positive number'
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
      text: form.text,
      type: form.type,
      required: form.required,
      order: form.order,
      score_weight: form.score_weight,
      section_id: form.section_id
    }
    
    let response
    
    if (props.isEditMode && props.questionData) {
      // Update existing question
      response = await axios.put(`/api/questions/${props.questionData.id}`, submitData)
    } else {
      // Create new question
      response = await axios.post('/api/questions', submitData)
    }
    
    if (response.data.success) {
      showToast(response.data.message, 'success')
      emit('success', response.data.data)
      emit('close')
      resetForm()
    } else {
      throw new Error(response.data.message || 'Failed to save question')
    }
  } catch (error) {
    console.error('Error submitting question:', error)
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = validationErrors[key][0]
        }
      })
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to save question'
      showToast(errorMessage, 'error')
    }
  } finally {
    loading.value = false
  }
}



// Watch for drawer open/close and questionData changes
watch(() => props.isOpen, (newValue) => {
  if (newValue && props.isEditMode && props.questionData) {
    // Fill form with question data for editing
    form.text = props.questionData.text || ''
    form.type = props.questionData.type || ''
    form.required = props.questionData.required || false
    form.order = props.questionData.order || 1
    form.score_weight = props.questionData.score_weight || null
    form.section_id = props.questionData.section_id || props.sectionId
  } else if (!newValue) {
    resetForm()
  }
})

watch(() => props.questionData, (newValue) => {
  if (props.isOpen && props.isEditMode && newValue) {
    // Fill form with question data when questionData changes
    form.text = newValue.text || ''
    form.type = newValue.type || ''
    form.required = newValue.required || false
    form.order = newValue.order || 1
    form.score_weight = newValue.score_weight || null
    form.section_id = newValue.section_id || props.sectionId
  }
})

// Watch for sectionId changes
watch(() => props.sectionId, (newSectionId) => {
  form.section_id = newSectionId
}, { immediate: true })
</script>