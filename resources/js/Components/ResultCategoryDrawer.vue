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

        <!-- Result Category Selection -->
        <div class="form-control mb-4">
          <label class="text-sm label">
            <span class="label-text">Select Result Category</span>
            <span class="label-text-alt text-error">*</span>
          </label>
          <select 
            v-model="selectedCategoryId"
            @change="onCategoryChange"
            class="w-full select select-bordered select-sm"
            :class="{ 'select-error': errors.category }"
          >
            <option value="">Choose a result category...</option>
            <option 
              v-for="category in resultCategories" 
              :key="category.id" 
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <label v-if="errors.category" class="label">
            <span class="label-text-alt text-error">{{ errors.category }}</span>
          </label>
        </div>

        <!-- Selected Category Details -->
        <div v-if="selectedCategory" class="mb-4 p-3 bg-base-200 rounded-lg">
          <h4 class="font-medium text-sm mb-2">Category Details:</h4>
          <div class="space-y-1 text-xs">
            <p><span class="font-medium">Name:</span> {{ selectedCategory.name }}</p>
            
            <p><span class="font-medium">Color:</span> 
              <span class="badge badge-sm" :class="`badge-${selectedCategory.color}`">{{ selectedCategory.color }}</span>
            </p>
          </div>
        </div>

        <!-- Result Category Rules Management -->
        <div v-if="selectedCategory">
          <!-- Existing Rules List -->
          <div v-if="categoryRules.length > 0" class="mb-4">
            <h4 class="font-medium text-sm mb-2">Existing Rules:</h4>
            <div class="space-y-2">
              <div 
                v-for="rule in categoryRules" 
                :key="rule.id"
                class="flex items-center justify-between p-2 bg-base-100 rounded border"
              >
                <div class="flex-1">
                  <div class="text-sm font-medium">{{ rule.title }}</div>
                  <div class="text-xs text-base-content/70">
                    {{ rule.operation === 'lt' ? 'Less than' : rule.operation === 'gt' ? 'Greater than' : 'Default' }} 
                    <span v-if="rule.operation !== 'else'">{{ rule.score }}%</span>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <span class="badge badge-sm" :class="`badge-${rule.color}`">{{ rule.color }}</span>
                  <button 
                    type="button"
                    @click="editRule(rule)"
                    class="btn btn-xs btn-ghost"
                  >
                    Edit
                  </button>
                  <button 
                    type="button"
                    @click="deleteRule(rule.id)"
                    class="btn btn-xs btn-error btn-ghost"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Rule Form -->
          <form @submit.prevent="handleRuleSubmit" class="space-y-3">
            <h4 class="font-medium text-sm">{{ editingRule ? 'Edit Rule' : 'Add New Rule' }}</h4>
            
            <!-- Operation Field -->
            <div class="form-control">
              <label class="text-sm label">
                <span class="label-text">Operation</span>
                <span class="label-text-alt text-error">*</span>
              </label>
              <select 
                v-model="ruleForm.operation"
                class="w-full select select-bordered select-sm"
                :class="{ 'select-error': errors.operation }"
                required
              >
                <option value="">Select operation</option>
                <option value="lt">Less than (&lt;)</option>
                <option value="gt">Greater than (&gt;)</option>
                <option value="else">Default (else)</option>
              </select>
              <label v-if="errors.operation" class="label">
                <span class="label-text-alt text-error">{{ errors.operation }}</span>
              </label>
            </div>

            <!-- Score Field -->
            <div class="form-control" v-if="ruleForm.operation !== 'else'">
              <label class="text-sm label">
                <span class="label-text">Score (%)</span>
                <span class="label-text-alt text-error">*</span>
              </label>
              <input 
                type="number" 
                v-model.number="ruleForm.score"
                placeholder="Enter score percentage" 
                class="w-full input input-bordered input-sm"
                :class="{ 'input-error': errors.score }"
                min="0"
                max="100"
                step="0.01"
                required
              />
              <label v-if="errors.score" class="label">
                <span class="label-text-alt text-error">{{ errors.score }}</span>
              </label>
            </div>

            <!-- Title Field -->
            <div class="form-control">
              <label class="text-sm label">
                <span class="label-text">Rule Title</span>
                <span class="label-text-alt text-error">*</span>
              </label>
              <input 
                type="text" 
                v-model="ruleForm.title"
                placeholder="Enter rule title" 
                class="w-full input input-bordered input-sm"
                :class="{ 'input-error': errors.title }"
                required
              />
              <label v-if="errors.title" class="label">
                <span class="label-text-alt text-error">{{ errors.title }}</span>
              </label>
            </div>

            <!-- Color Field -->
            <div class="form-control">
              <label class="text-sm label">
                <span class="label-text">Color</span>
                <span class="label-text-alt text-error">*</span>
              </label>
              <select 
                v-model="ruleForm.color"
                class="w-full select select-bordered select-sm"
                :class="{ 'select-error': errors.color }"
                required
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
                v-if="editingRule"
                type="button" 
                class="btn btn-sm btn-ghost"
                @click="cancelEdit"
                :disabled="loading"
              >
                Cancel Edit
              </button>
              <button 
                type="submit" 
                class="flex-1 btn btn-primary btn-sm"
                :disabled="loading || !selectedCategoryId"
              >
                <span v-if="loading" class="loading loading-spinner loading-xs"></span>
                {{ loading ? (editingRule ? 'Updating...' : 'Creating...') : (editingRule ? 'Update Rule' : 'Add Rule') }}
              </button>
            </div>
          </form>
        </div>
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
    default: 'Result Category Rules'
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
const resultCategories = ref([])
const selectedCategoryId = ref('')
const selectedCategory = ref(null)
const categoryRules = ref([])
const editingRule = ref(null)

const ruleForm = reactive({
  operation: '',
  title: '',
  score: 0,
  color: ''
})

const errors = reactive({
  category: '',
  operation: '',
  title: '',
  score: '',
  color: ''
})

// Methods
const closeDrawer = () => {
  emit('close')
}

const resetForm = () => {
  selectedCategoryId.value = ''
  selectedCategory.value = null
  categoryRules.value = []
  editingRule.value = null
  
  ruleForm.operation = ''
  ruleForm.title = ''
  ruleForm.score = 0
  ruleForm.color = ''
  
  // Clear errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
}

const resetRuleForm = () => {
  ruleForm.operation = ''
  ruleForm.title = ''
  ruleForm.score = 0
  ruleForm.color = ''
  editingRule.value = null
  
  // Clear rule-related errors
  errors.operation = ''
  errors.title = ''
  errors.score = ''
  errors.color = ''
}

const fetchResultCategories = async () => {
  try {
    const response = await axios.get(`/api/surveys/${props.surveyId}/result-categories`)
    if (response.data.success) {
      resultCategories.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching result categories:', error)
    showToast('Failed to load result categories', 'error')
  }
}

const fetchCategoryRules = async (categoryId) => {
  try {
    const response = await axios.get(`/api/result-categories/${categoryId}/rules`)
    if (response.data.success) {
      categoryRules.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching category rules:', error)
    showToast('Failed to load category rules', 'error')
  }
}

const onCategoryChange = async () => {
  if (selectedCategoryId.value) {
    selectedCategory.value = resultCategories.value.find(cat => cat.id == selectedCategoryId.value)
    await fetchCategoryRules(selectedCategoryId.value)
    resetRuleForm()
  } else {
    selectedCategory.value = null
    categoryRules.value = []
    resetRuleForm()
  }
}

const validateRuleForm = () => {
  let isValid = true
  
  // Clear previous errors
  errors.operation = ''
  errors.title = ''
  errors.score = ''
  errors.color = ''
  
  // Operation validation
  if (!ruleForm.operation) {
    errors.operation = 'Operation is required'
    isValid = false
  }
  
  // Title validation
  if (!ruleForm.title.trim()) {
    errors.title = 'Rule title is required'
    isValid = false
  }
  
  // Score validation (only for lt and gt operations)
  if (ruleForm.operation !== 'else' && (ruleForm.score < 0 || ruleForm.score > 100)) {
    errors.score = 'Score must be between 0 and 100'
    isValid = false
  }
  
  // Color validation
  if (!ruleForm.color) {
    errors.color = 'Color is required'
    isValid = false
  }
  
  return isValid
}

const handleRuleSubmit = async () => {
  if (!validateRuleForm()) {
    return
  }
  
  loading.value = true
  
  try {
    const submitData = {
      operation: ruleForm.operation,
      title: ruleForm.title,
      score: ruleForm.operation === 'else' ? 0 : ruleForm.score,
      color: ruleForm.color
    }
    
    let response
    
    if (editingRule.value) {
      // Update existing rule
      response = await axios.put(`/api/result-categories/${selectedCategoryId.value}/rules/${editingRule.value.id}`, submitData)
    } else {
      // Create new rule
      response = await axios.post(`/api/result-categories/${selectedCategoryId.value}/rules`, submitData)
    }
    
    if (response.data.success) {
      showToast(response.data.message, 'success')
      await fetchCategoryRules(selectedCategoryId.value) // Refresh rules list
      resetRuleForm()
      emit('success', response.data.data)
    } else {
      throw new Error(response.data.message || 'Failed to save rule')
    }
  } catch (error) {
    console.error('Error submitting rule:', error)
    
    // Handle validation errors
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const validationErrors = error.response.data.errors
      Object.keys(validationErrors).forEach(key => {
        if (errors.hasOwnProperty(key)) {
          errors[key] = validationErrors[key][0]
        }
      })
    } else {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to save rule'
      showToast(errorMessage, 'error')
    }
  } finally {
    loading.value = false
  }
}

const editRule = (rule) => {
  editingRule.value = rule
  ruleForm.operation = rule.operation
  ruleForm.title = rule.title
  ruleForm.score = rule.score
  ruleForm.color = rule.color
}

const cancelEdit = () => {
  resetRuleForm()
}

const deleteRule = async (ruleId) => {
  if (!confirm('Are you sure you want to delete this rule?')) {
    return
  }
  
  try {
    const response = await axios.delete(`/api/result-categories/${selectedCategoryId.value}/rules/${ruleId}`)
    
    if (response.data.success) {
      showToast(response.data.message, 'success')
      await fetchCategoryRules(selectedCategoryId.value) // Refresh rules list
      emit('success')
    } else {
      throw new Error(response.data.message || 'Failed to delete rule')
    }
  } catch (error) {
    console.error('Error deleting rule:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Failed to delete rule'
    showToast(errorMessage, 'error')
  }
}

// Watch for drawer open/close
watch(() => props.isOpen, async (newValue) => {
  if (newValue) {
    // Fetch result categories when drawer opens
    await fetchResultCategories()
  } else {
    // Reset form when drawer closes
    resetForm()
  }
})

// Watch for surveyId changes
watch(() => props.surveyId, async (newSurveyId) => {
  if (newSurveyId && props.isOpen) {
    await fetchResultCategories()
  }
}, { immediate: false })
</script>