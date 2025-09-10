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
        <div class="mb-4 form-control">
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

        <!-- Result Category Rules Management -->
        <div v-if="selectedCategory">
          <!-- Rules Management -->
          <div class="space-y-3">
            <div v-for="(rule, index) in categoryRules" :key="rule.id || `new-${index}`">
              <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box">
                <legend class="flex justify-between items-center w-full fieldset-legend">
                   <span>Rule {{ index + 1 }}</span>
                   <button v-if="categoryRules.length > 1 && rule.operation !== 'else'" type="button" @click="deleteRule(rule.id)"
                     class="ml-2 btn btn-xs btn-ghost btn-circle text-error">
                     <X :size="12" />
                   </button>
                 </legend>

                <div class="space-y-3">
                  <!-- Operation Field -->
                  <div>
                    <label class="label">
                      <span class="label-text">Operation</span>
                      <span class="label-text-alt text-error">*</span>
                    </label>
                    <div v-if="rule.operation === 'else'" class="w-full text-gray-600 bg-gray-100 cursor-not-allowed input input-bordered input-sm">
                      Default (else)
                    </div>
                    <select 
                      v-else
                      v-model="rule.operation"
                      class="w-full select select-bordered select-sm"
                      required
                    >
                      <option value="">Select operation</option>
                      <option value="lt">Less than (&lt;)</option>
                      <option value="gt">Greater than (&gt;)</option>
                    </select>
                  </div>

                  <!-- Score and Title Fields -->
                  <div class="grid grid-cols-2 gap-3">
                    <div v-if="rule.operation !== 'else'">
                      <label class="label">
                        <span class="label-text">Score (%)</span>
                      </label>
                      <input 
                        v-model.number="rule.score" 
                        type="number" 
                        placeholder="Score percentage"
                        class="w-full input input-bordered input-sm" 
                        min="0" 
                        max="100" 
                        step="0.01" 
                      />
                    </div>

                    <div :class="rule.operation === 'else' ? 'col-span-2' : ''">
                      <label class="label">
                        <span class="label-text">Rule Title</span>
                        <span class="label-text-alt text-error">*</span>
                      </label>
                      <input 
                        v-model="rule.title" 
                        type="text" 
                        placeholder="Rule title"
                        class="w-full input input-bordered input-sm" 
                        required 
                      />
                    </div>
                  </div>

                  <!-- Color Field -->
                   <div>
                     <label class="label">
                       <span class="label-text">Color</span>
                       <span class="label-text-alt text-error">*</span>
                     </label>
                     <div class="flex flex-wrap gap-2">
                       <button 
                         v-for="color in colorOptions" 
                         :key="color.value"
                         type="button"
                         @click="rule.color = color.value"
                         :class="[
                           'w-8 h-8 rounded-full border-2 transition-all hover:scale-110',
                           `bg-${color.value}`,
                           rule.color === color.value
                             ? 'border-gray-800 ring-2 ring-offset-2 ring-gray-400'
                             : 'border-gray-300 hover:border-gray-500'
                         ]"
                         :title="color.label"
                       >
                       </button>
                     </div>
                   </div>
                </div>
              </fieldset>
            </div>
          </div>

          <button type="button" @click="addRule" class="mt-3 w-full btn btn-sm btn-outline">
            Add Rule
          </button>

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
              type="button" 
              class="flex-1 btn btn-primary btn-sm"
              @click="saveAllRules"
              :disabled="loading || !selectedCategoryId"
            >
              <span v-if="loading" class="loading loading-spinner loading-xs"></span>
              {{ loading ? 'Saving...' : 'Save Rules' }}
            </button>
          </div>
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

const errors = reactive({
  category: ''
})

const colorOptions = [
  { value: 'primary', label: 'Primary' },
  { value: 'secondary', label: 'Secondary' },
  { value: 'accent', label: 'Accent' },
  { value: 'success', label: 'Success' },
  { value: 'warning', label: 'Warning' },
  { value: 'error', label: 'Error' },
  { value: 'info', label: 'Info' }
]

// Methods
const closeDrawer = () => {
  emit('close')
}

const resetForm = () => {
  selectedCategoryId.value = ''
  selectedCategory.value = null
  categoryRules.value = []
  
  // Clear errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
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
    
    // Add default rule if no rules exist
    if (categoryRules.value.length === 0) {
      addRule()
    }
  } else {
    selectedCategory.value = null
    categoryRules.value = []
  }
}

const addRule = () => {
  categoryRules.value.push({
    operation: '',
    title: '',
    score: 0,
    color: ''
  })
}

const validateRules = () => {
  for (let i = 0; i < categoryRules.value.length; i++) {
    const rule = categoryRules.value[i]
    
    if (!rule.operation) {
      showToast(`Rule ${i + 1}: Operation is required`, 'error')
      return false
    }
    
    if (!rule.title?.trim()) {
      showToast(`Rule ${i + 1}: Title is required`, 'error')
      return false
    }
    
    if (rule.operation !== 'else' && (rule.score < 0 || rule.score > 100)) {
      showToast(`Rule ${i + 1}: Score must be between 0 and 100`, 'error')
      return false
    }
    
    if (!rule.color) {
      showToast(`Rule ${i + 1}: Color is required`, 'error')
      return false
    }
  }
  
  return true
}

const saveAllRules = async () => {
  if (!validateRules()) {
    return
  }
  
  loading.value = true
  
  try {
    // First, delete all existing rules
    const existingRules = categoryRules.value.filter(rule => rule.id)
    for (const rule of existingRules) {
      await axios.delete(`/api/result-categories/${selectedCategoryId.value}/rules/${rule.id}`)
    }
    
    // Then create all rules
    for (const rule of categoryRules.value) {
      const submitData = {
        operation: rule.operation,
        title: rule.title,
        score: rule.operation === 'else' ? 0 : rule.score,
        color: rule.color
      }
      
      await axios.post(`/api/result-categories/${selectedCategoryId.value}/rules`, submitData)
    }
    
    showToast('Rules saved successfully', 'success')
    await fetchCategoryRules(selectedCategoryId.value)
    emit('success')
  } catch (error) {
    console.error('Error saving rules:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Failed to save rules'
    showToast(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

const deleteRule = (ruleId) => {
  if (ruleId) {
    // Remove from array for existing rules
    const index = categoryRules.value.findIndex(rule => rule.id === ruleId)
    if (index > -1 && categoryRules.value[index].operation !== 'else') {
      categoryRules.value.splice(index, 1)
    }
  } else {
    // Remove last rule for new rules, but not if it's an 'else' operation
    const lastRule = categoryRules.value[categoryRules.value.length - 1]
    if (lastRule && lastRule.operation !== 'else') {
      categoryRules.value.pop()
    }
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