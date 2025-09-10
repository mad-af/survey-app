<template>
  <div class="border shadow-sm card bg-base-100 border-base-200">
    <div class="p-4 card-body">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold card-title text-base-content">
          <Target class="w-5 h-5" />
          Result Categories
        </h3>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-8">
        <span class="loading loading-spinner loading-md"></span>
      </div>

      <!-- Empty State -->
      <div v-else-if="resultCategories.length === 0" class="py-8 text-center">
        <div class="text-base-content/60">
          <Target class="mx-auto mb-3 w-12 h-12 opacity-50" />
          <p class="text-sm">No result categories found</p>
        </div>
      </div>

      <!-- Result Categories List -->
      <div v-else class="join join-vertical bg-base-100">
        <div v-for="(category, index) in resultCategories" :key="category.id" class="border collapse collapse-arrow join-item border-base-300">
          <input type="radio" :name="`result-category-accordion`" :checked="index === 0" />
          <div class="text-xs font-semibold tracking-wider uppercase collapse-title text-primary">{{ getCategoryDisplayName(category) }}</div>
          <div class="text-xs collapse-content">
            <div v-if="category.rules && category.rules.length > 0" class="space-y-2">
              <div v-for="rule in category.rules" :key="rule.id" class="flex gap-2 items-center">
                <span :class="getRuleBadgeClass(rule.color)" class="badge badge-xs">{{ formatRuleOperation(rule) }}</span>
                <span>{{ rule.title }}</span>
              </div>
            </div>
            <div v-else class="text-base-content/60">
              No rules defined
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Target } from 'lucide-vue-next'
import axios from 'axios'

// Props
const props = defineProps({
  surveyId: {
    type: [String, Number],
    required: true
  }
})

// Reactive data
const resultCategories = ref([])
const loading = ref(false)

// Methods
const fetchResultCategories = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/surveys/${props.surveyId}/result-categories`)
    resultCategories.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching result categories:', error)
    resultCategories.value = []
  } finally {
    loading.value = false
  }
}

const getRuleBadgeClass = (color) => {
  const classes = {
    'error': 'badge-error',
    'success': 'badge-success', 
    'warning': 'badge-warning',
    'info': 'badge-info'
  }
  return classes[color] || 'badge-ghost'
}

const getRuleColorClass = (color) => {
  const classes = {
    'error': 'bg-error/10 text-error',
    'success': 'bg-success/10 text-success',
    'warning': 'bg-warning/10 text-warning', 
    'info': 'bg-info/10 text-info'
  }
  return classes[color] || 'bg-base-200 text-base-content'
}

const formatRuleOperation = (rule) => {
  const operation = rule.operation.toLowerCase()
  const score = rule.score || 0
  
  if (operation === 'gt') {
    return `${score} >`
  } else if (operation === 'lt') {
    return `${score} <`
  } else {
    return 'default'
  }
}

const getCategoryDisplayName = (category) => {
  if (category.owner_type === 'survey') {
    return 'survey'
  } else if (category.owner_type === 'survey_section') {
    const sectionNumber = category.section_order || 1
    return `# section ${sectionNumber}`
  }
  return category.name
}

// Lifecycle
onMounted(() => {
  fetchResultCategories()
})

// Expose methods for parent component
defineExpose({
  fetchResultCategories
})
</script>