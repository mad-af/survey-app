<template>
  <DashboardLayout 
    :pageTitle="'Survey Responses'"
  >
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader 
        :title="survey?.title ? `Responses - ${survey.title}` : 'Survey Responses'" 
        description="View all responses and respondent data for this survey"
        :showBackButton="true"
      />

      <!-- Survey Statistics Card -->
      <SurveyStatisticsCard 
        :total-responses="totalResponses"
        :completed-responses="completedResponses"
        :in-progress-responses="inProgressResponses"
        :started-responses="startedResponses"
        :abandoned-responses="abandonedResponses"
        :completion-rate="completionRate"
      />

      <!-- Responses Table -->
      <div class="border shadow-sm card bg-base-100 border-base-200">
        <div class="card-body">
          <div class="flex flex-col gap-4 mb-4 md:flex-row md:justify-between md:items-center">
            <div class="card-title">
              <h3 class="text-lg font-semibold">Response Details</h3>
              <div class="text-sm font-normal text-base-content/60">
                Showing {{ filteredResponses.length }} of {{ responses.length }} responses
                <span v-if="searchQuery || statusFilter" class="ml-2 badge badge-sm badge-info">
                  Filtered
                </span>
              </div>
            </div>
            
            <!-- Filters and Search -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center">
              <div class="form-control">
                <input 
                  type="text" 
                  placeholder="Search responses..." 
                  class="w-full input input-bordered input-sm md:w-64"
                  v-model="searchQuery"
                />
              </div>
              <div class="form-control">
                <select class="select select-bordered select-sm" v-model="statusFilter">
                  <option value="">All Status</option>
                  <option value="completed">Completed</option>
                  <option value="in_progress">In Progress</option>
                  <option value="started">Started</option>
                  <option value="abandoned">Abandoned</option>
                </select>
              </div>
              <button 
                class="btn btn-sm btn-outline btn-primary"
                @click="refreshData"
                title="Refresh Data"
              >
                <RefreshCw class="w-4 h-4" />
              </button>
              <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-sm btn-success">
                  <Download class="w-4 h-4" />
                  Export
                </div>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                  <li>
                    <a @click="exportResponses('all', 'excel')" class="flex gap-2 items-center">
                      <Download class="w-4 h-4" />
                      Export as Excel
                    </a>
                  </li>
                  <li>
                    <a @click="exportResponses('all', 'pdf')" class="flex gap-2 items-center">
                      <Download class="w-4 h-4" />
                      Export as PDF
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <DataTable
              v-if="filteredResponses.length > 0"
              :data="filteredResponses"
              :columns="tableColumns"
              :actions="tableActions"
              :items-per-page="15"
              @view-response="viewResponse"
            />
          
          <div v-else class="py-8 text-center">
            <div class="text-base-content/60">
              <FileText class="mx-auto mb-4 w-12 h-12 opacity-50" />
              <p class="mb-2 text-lg font-medium">
                {{ (searchQuery || statusFilter) ? 'No matching responses found' : 'No responses yet' }}
              </p>
              <p class="text-sm">
                {{ (searchQuery || statusFilter) ? 'Try adjusting your search or filter criteria.' : 'Responses will appear here once participants start submitting the survey.' }}
              </p>
              <div v-if="searchQuery || statusFilter" class="mt-4">
                <button 
                  class="btn btn-sm btn-outline" 
                  @click="searchQuery = ''; statusFilter = ''"
                >
                  Clear Filters
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Response Detail Modal -->
      <ResponseDetailModal 
        :response="selectedResponse" 
        :survey="survey" 
        @close="closeModal" 
      />



    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Eye, RefreshCw, FileText, X, BarChart3, Download } from 'lucide-vue-next'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SurveyStatisticsCard from '@/Components/SurveyStatisticsCard.vue'
import DataTable from '@/Components/DataTable.vue'
import ResponseDetailModal from '@/Components/ResponseDetailModal.vue'

// Props from backend
const props = defineProps({
  survey: {
    type: Object,
    default: () => ({})
  },
  responses: {
    type: Array,
    default: () => []
  },
  statistics: {
    type: Object,
    default: () => ({
      total: 0,
      completed: 0,
      in_progress: 0,
      started: 0,
      abandoned: 0,
      completion_rate: 0
    })
  }
})

// Reactive data
const selectedResponse = ref(null)
const searchQuery = ref('')
const statusFilter = ref('')

// Computed properties
const filteredResponses = computed(() => {
  let filtered = props.responses
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(response => 
      response.respondent?.name?.toLowerCase().includes(query) ||
      response.respondent?.email?.toLowerCase().includes(query) ||
      response.respondent?.organization?.toLowerCase().includes(query) ||
      response.respondent?.department?.toLowerCase().includes(query) ||
      response.respondent?.location?.province_name?.toLowerCase().includes(query) ||
      response.respondent?.location?.regency_name?.toLowerCase().includes(query) ||
      response.respondent?.location?.district_name?.toLowerCase().includes(query) ||
      response.respondent?.location?.village_name?.toLowerCase().includes(query) ||
      response.respondent_token?.toLowerCase().includes(query)
    )
  }
  
  // Filter by status
  if (statusFilter.value) {
    filtered = filtered.filter(response => response.status === statusFilter.value)
  }
  
  return filtered
})

// Statistics computed properties (using data from backend)
const totalResponses = computed(() => props.statistics.total)
const completedResponses = computed(() => props.statistics.completed)
const inProgressResponses = computed(() => props.statistics.in_progress)
const startedResponses = computed(() => props.statistics.started)
const abandonedResponses = computed(() => props.statistics.abandoned)
const completionRate = computed(() => props.statistics.completion_rate)

// Table configuration
const tableColumns = computed(() => [
  {
    key: 'id',
    label: 'ID',
    formatter: (value) => `#${value}`
  },
  {
    key: 'respondent.name',
    label: 'Respondent',
    formatter: (value, item) => item.respondent?.name || 'Anonymous'
  },
  {
    key: 'respondent.email',
    label: 'Email',
    formatter: (value, item) => item.respondent?.email || '-'
  },
  {
    key: 'location',
    label: 'Location',
    formatter: (value, item) => {
      if (!item.respondent?.location) return '-'
      const regency = item.respondent.location.regency_name || '-'
      const province = item.respondent.location.province_name || '-'
      return regency !== '-' && province !== '-' ? `${regency}, ${province}` : (regency !== '-' ? regency : province)
    }
  },
  {
    key: 'current_step',
    label: 'Progress',
    type: 'badge',
    formatter: (value, item) => getStepLabel(value),
    class: (value, item) => getStepBadgeClass(value)
  },
  {
    key: 'status',
    label: 'Status',
    type: 'badge',
    formatter: (value) => getStatusLabel(value),
    class: (value) => getStatusBadgeClass(value)
  },
  {
    key: 'score',
    label: 'Score',
    formatter: (value, item) => {
      if (!item.score) return '-'
      return `${item.score.total_score}/${item.score.max_possible_score} (${item.score.percentage}%)`
    }
  },
  {
    key: 'started_at',
    label: 'Started',
    type: 'date',
    formatter: (value) => formatDate(value)
  },
  {
    key: 'submitted_at',
    label: 'Submitted',
    type: 'date',
    formatter: (value, item) => formatDate(value || item.updated_at)
  }
])

const tableActions = computed(() => [
  {
    name: 'view',
    label: 'View Details',
    icon: Eye,
    event: 'view-response',
    class: 'btn-sm'
  }
])

// Methods
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'completed':
      return 'badge-success'
    case 'in_progress':
      return 'badge-warning'
    case 'started':
      return 'badge-info'
    case 'abandoned':
      return 'badge-error'
    default:
      return 'badge-ghost'
  }
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'completed':
      return 'Completed'
    case 'in_progress':
      return 'In Progress'
    case 'started':
      return 'Started'
    case 'abandoned':
      return 'Abandoned'
    default:
      return 'Unknown'
  }
}

const getStepBadgeClass = (step) => {
  switch (step) {
    case 1:
      return 'badge-info'
    case 2:
      return 'badge-warning'
    case 3:
      return 'badge-success'
    default:
      return 'badge-ghost'
  }
}

const getStepLabel = (step) => {
  switch (step) {
    case 1:
      return 'Respondent Data'
    case 2:
      return 'Questions'
    case 3:
      return 'Result'
    default:
      return 'Unknown'
  }
}

const getGenderLabel = (gender) => {
  switch (gender) {
    case 'male':
      return 'Male'
    case 'female':
      return 'Female'
    case 'other':
      return 'Other'
    case 'prefer_not_to_say':
      return 'Prefer not to say'
    default:
      return 'Not specified'
  }
}

const getCategoryBadgeClass = (color) => {
  if (!color) return 'badge-ghost'
  
  const colorMap = {
    'red': 'badge-error',
    'yellow': 'badge-warning',
    'green': 'badge-success',
    'blue': 'badge-info',
    'purple': 'badge-secondary',
    'gray': 'badge-ghost'
  }
  
  return colorMap[color] || 'badge-ghost'
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return '-'
  }
}

const formatDuration = (startDate, endDate) => {
  if (!startDate || !endDate) return '-'
  try {
    const start = new Date(startDate)
    const end = new Date(endDate)
    const diffMs = end - start
    const diffMins = Math.floor(diffMs / 60000)
    const diffHours = Math.floor(diffMins / 60)
    const remainingMins = diffMins % 60
    
    if (diffHours > 0) {
      return `${diffHours}h ${remainingMins}m`
    } else {
      return `${diffMins}m`
    }
  } catch (error) {
    return '-'
  }
}

const getFullAddress = (location) => {
  if (!location) return '-'
  
  const parts = []
  if (location.village_name) parts.push(location.village_name)
  if (location.district_name) parts.push(location.district_name)
  if (location.regency_name) parts.push(location.regency_name)
  if (location.province_name) parts.push(location.province_name)
  
  let address = parts.join(', ')
  
  if (location.detailed_address) {
    address = location.detailed_address + ', ' + address
  }
  
  return address || '-'
}

const getSectionTitle = (sectionId) => {
  if (!props.survey?.sections) return `Section ${sectionId}`
  const section = props.survey.sections.find(s => s.id === sectionId)
  return section ? section.title : `Section ${sectionId}`
}

const getTotalQuestions = computed(() => {
  if (!selectedSurveyForStructure.value?.sections) return 0
  return selectedSurveyForStructure.value.sections.reduce((total, section) => {
    return total + (section.questions ? section.questions.length : 0)
  }, 0)
})

// Helper functions for survey structure modal
const getQuestionCount = (section) => {
  return section.questions ? section.questions.length : 0
}

const getQuestionTypeBadgeClass = (type) => {
  const typeClasses = {
    'text': 'badge-primary',
    'textarea': 'badge-secondary', 
    'radio': 'badge-accent',
    'checkbox': 'badge-info',
    'select': 'badge-success',
    'number': 'badge-warning',
    'date': 'badge-error'
  }
  return typeClasses[type] || 'badge-ghost'
}

const getQuestionTypeLabel = (type) => {
  const typeLabels = {
    'text': 'Text Input',
    'textarea': 'Long Text',
    'radio': 'Single Choice',
    'checkbox': 'Multiple Choice', 
    'select': 'Dropdown',
    'number': 'Number',
    'date': 'Date'
  }
  return typeLabels[type] || type
}

const getQuestionAnswers = (questionId) => {
  // Only get answers from the selected response, not all responses
  if (!selectedResponse.value || !selectedResponse.value.answers) return []
  
  const questionAnswers = selectedResponse.value.answers.filter(answer => answer.question_id === questionId)
  const answers = questionAnswers.map(answer => ({
    ...answer,
    response: selectedResponse.value
  }))
  
  return answers
}

const formatAnswerValue = (answer) => {
  // Handle choice answers
  if (answer.choice_id) {
    const choiceText = getChoiceText(answer.choice_id)
    return choiceText || `Choice ${answer.choice_id}`
  }
  
  // Handle text answers
  if (answer.value_text) {
    if (answer.value_text.length > 50) {
      return answer.value_text.substring(0, 50) + '...'
    }
    return answer.value_text
  }
  
  // Handle number answers
  if (answer.value_number !== null && answer.value_number !== undefined) {
    return answer.value_number.toString()
  }
  
  // Handle JSON answers
  if (answer.value_json) {
    try {
      const jsonData = typeof answer.value_json === 'string' ? JSON.parse(answer.value_json) : answer.value_json
      const jsonStr = JSON.stringify(jsonData, null, 2)
      if (jsonStr.length > 50) {
        return jsonStr.substring(0, 50) + '...'
      }
      return jsonStr
    } catch (e) {
      return 'Invalid JSON'
    }
  }
  
  return 'No answer'
}

// Helper functions for question and answer details
const getQuestionText = (questionId) => {
  if (!props.survey?.sections) return `Question ${questionId}`
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.text
    }
  }
  return `Question ${questionId}`
}

const getQuestionType = (questionId) => {
  if (!props.survey?.sections) return 'text'
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.type
    }
  }
  return 'text'
}

const isQuestionRequired = (questionId) => {
  if (!props.survey?.sections) return false
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.required
    }
  }
  return false
}

const getQuestionSectionId = (questionId) => {
  if (!props.survey?.sections) return null
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return section.id
    }
  }
  return null
}

const getQuestionOrder = (questionId) => {
  if (!props.survey?.sections) return 0
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.order
    }
  }
  return 0
}

const getChoiceText = (choiceId) => {
  if (!props.survey?.sections) return `Choice ${choiceId}`
  for (const section of props.survey.sections) {
    if (section.questions) {
      for (const question of section.questions) {
        if (question.choices) {
          const choice = question.choices.find(c => c.id === choiceId)
          if (choice) return choice.text
        }
      }
    }
  }
  return `Choice ${choiceId}`
}

const getChoiceScore = (choiceId) => {
  if (!props.survey?.sections) return null
  for (const section of props.survey.sections) {
    if (section.questions) {
      for (const question of section.questions) {
        if (question.choices) {
          const choice = question.choices.find(c => c.id === choiceId)
          if (choice) return choice.score
        }
      }
    }
  }
  return null
}

const formatJsonAnswer = (jsonValue) => {
  if (!jsonValue) return 'No answer'
  if (Array.isArray(jsonValue)) {
    return jsonValue.join(', ')
  }
  if (typeof jsonValue === 'object') {
    return JSON.stringify(jsonValue, null, 2)
  }
  return String(jsonValue)
}

// Functions
const viewResponse = async (response) => {
  try {
    // If response already has answers loaded, use it directly
    if (response.answers && response.answers.length > 0) {
      selectedResponse.value = response
    } else {
      // Load response details with answers from API
      const apiResponse = await fetch(`/dashboard/survey/${props.survey.id}/responses/${response.id}/details`)
      if (apiResponse.ok) {
        const data = await apiResponse.json()
        selectedResponse.value = data.response
      } else {
        // Fallback to original response if API fails
        selectedResponse.value = response
      }
    }
  } catch (error) {
    console.error('Error loading response details:', error)
    // Fallback to original response if API fails
    selectedResponse.value = response
  }
}

const closeModal = () => {
  selectedResponse.value = null
}



const refreshData = () => {
  // TODO: Implement refresh functionality
  window.location.reload()
}

// Export functions
const exportResponses = async (type, format = 'excel') => {
  try {
    // Build query parameters
    const params = new URLSearchParams({
      type: type,
      format: format
    })
    
    // Add filters for 'filtered' export type
    if (type === 'filtered') {
      if (statusFilter.value) {
        params.append('status_filter', statusFilter.value)
      }
      if (searchQuery.value) {
        params.append('search_query', searchQuery.value)
      }
    }
    
    // Create export URL
    const exportUrl = `/dashboard/survey/${props.survey.id}/responses/export?${params.toString()}`
    
    // Create a temporary link to trigger download
    const link = document.createElement('a')
    link.href = exportUrl
    link.download = ''
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
  } catch (error) {
    console.error('Export failed:', error)
    alert('Export failed. Please try again.')
  }
}
</script>