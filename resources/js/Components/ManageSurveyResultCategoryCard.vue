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
        <div v-for="(category, index) in sortedResultCategories" :key="category.id" class="border collapse collapse-arrow join-item border-base-300">
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
import { ref, onMounted, computed } from 'vue'
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

// Computed properties
const sortedResultCategories = computed(() => {
  return resultCategories.value.slice().sort((a, b) => {
    // Survey categories always come first
    if (a.owner_type === 'survey' && b.owner_type !== 'survey') return -1
    if (a.owner_type !== 'survey' && b.owner_type === 'survey') return 1
    
    // Then sort by name A-Z
    const nameA = (a.name || '').toLowerCase()
    const nameB = (b.name || '').toLowerCase()
    return nameA < nameB ? -1 : nameA > nameB ? 1 : 0
  })
})

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
    'primary': 'badge-primary',
    'secondary': 'badge-secondary',
    'accent': 'badge-accent',
    'success': 'badge-success',
    'warning': 'badge-warning',
    'error': 'badge-error',
    'info': 'badge-info'
  }
  return classes[color] || 'badge-ghost'
}

const formatRuleOperation = (rule) => {
  const operation = rule.operation.toLowerCase()
  const score = Math.round(rule.score || 0)
  
  if (operation === 'gt') {
    return `${score}% >`
  } else if (operation === 'lt') {
    return `${score}% <`
  } else {
    return 'default'
  }
}

const getCategoryDisplayName = (category) => {
  if (category.owner_type === 'survey') {
    return 'survey'
  } else if (category.owner_type === 'survey_section') {
    const categoryName = category.name 
    return `# ${categoryName}`
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