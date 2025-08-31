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
      <div v-if="selectedResponse" class="modal modal-open">
        <div class="max-w-4xl modal-box">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Response Details - #{{ selectedResponse.id }}</h3>
            <button class="btn btn-sm btn-circle btn-ghost" @click="selectedResponse = null">
              <X class="w-4 h-4" />
            </button>
          </div>
          
          <!-- Response Overview -->
          <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Status & Progress</h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span>Status:</span>
                    <span class="badge" :class="getStatusBadgeClass(selectedResponse.status)">
                      {{ getStatusLabel(selectedResponse.status) }}
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span>Progress:</span>
                    <span class="badge" :class="getStepBadgeClass(selectedResponse.current_step)">
                      {{ getStepLabel(selectedResponse.current_step) }}
                    </span>
                  </div>
                  <div class="flex justify-between">
                    <span>Started:</span>
                    <span>{{ formatDate(selectedResponse.started_at) }}</span>
                  </div>
                  <div v-if="selectedResponse.submitted_at" class="flex justify-between">
                    <span>Submitted:</span>
                    <span>{{ formatDate(selectedResponse.submitted_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Personal Information</h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span>Name:</span>
                    <span>{{ selectedResponse.respondent?.name || 'Anonymous' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Email:</span>
                    <span>{{ selectedResponse.respondent?.email || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Phone:</span>
                    <span>{{ selectedResponse.respondent?.phone || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Gender:</span>
                    <span>{{ getGenderLabel(selectedResponse.respondent?.gender) || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Birth Year:</span>
                    <span>{{ selectedResponse.respondent?.birth_year || '-' }}</span>
                  </div>
                  <div v-if="selectedResponse.respondent?.external_id" class="flex justify-between">
                    <span>External ID:</span>
                    <span>{{ selectedResponse.respondent.external_id }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Professional Information</h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span>Organization:</span>
                    <span>{{ selectedResponse.respondent?.organization || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Department:</span>
                    <span>{{ selectedResponse.respondent?.department || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Role:</span>
                    <span>{{ selectedResponse.respondent?.role_title || '-' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Location Information -->
          <div v-if="selectedResponse.respondent?.location" class="mb-6">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Location Information</h4>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span>Province:</span>
                    <span>{{ selectedResponse.respondent.location.province_name || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Regency:</span>
                    <span>{{ selectedResponse.respondent.location.regency_name || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>District:</span>
                    <span>{{ selectedResponse.respondent.location.district_name || '-' }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Village:</span>
                    <span>{{ selectedResponse.respondent.location.village_name || '-' }}</span>
                  </div>
                  <div v-if="selectedResponse.respondent.location.detailed_address" class="flex justify-between">
                    <span>Detailed Address:</span>
                    <span>{{ selectedResponse.respondent.location.detailed_address }}</span>
                  </div>
                  <div v-if="selectedResponse.respondent.location.latitude && selectedResponse.respondent.location.longitude" class="flex justify-between">
                    <span>Coordinates:</span>
                    <span>{{ selectedResponse.respondent.location.latitude }}, {{ selectedResponse.respondent.location.longitude }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Full Address:</span>
                    <span>{{ getFullAddress(selectedResponse.respondent.location) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Score Information -->
          <div v-if="selectedResponse.score" class="mb-6">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Overall Score Summary</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span>Total Score:</span>
                      <span class="font-semibold">{{ selectedResponse.score.total_score }}/{{ selectedResponse.score.max_possible_score }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span>Percentage:</span>
                      <span class="font-semibold">{{ selectedResponse.score.percentage }}%</span>
                    </div>
                  </div>
                  <div v-if="selectedResponse.score.result_category" class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span>Category:</span>
                      <span class="badge" :class="getCategoryBadgeClass(selectedResponse.score.result_category.color)">
                        {{ selectedResponse.score.result_category.name }}
                      </span>
                    </div>
                    <div class="flex justify-between">
                      <span>Score Range:</span>
                      <span>{{ selectedResponse.score.result_category.min_score }} - {{ selectedResponse.score.result_category.max_score }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section Scores -->
            <div v-if="selectedResponse.score.section_scores && selectedResponse.score.section_scores.length > 0" class="mb-6">
              <div class="card bg-base-200">
                <div class="card-body">
                  <h4 class="text-base card-title">Section-wise Results</h4>
                  <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div v-for="sectionScore in selectedResponse.score.section_scores" :key="sectionScore.section_id" class="p-3 rounded-lg bg-base-100">
                      <div class="flex justify-between items-center mb-2">
                        <h5 class="font-medium text-sm">{{ getSectionTitle(sectionScore.section_id) }}</h5>
                        <span class="text-xs badge badge-outline">Section {{ sectionScore.section_id }}</span>
                      </div>
                      <div class="space-y-1 text-xs">
                        <div class="flex justify-between">
                          <span>Score:</span>
                          <span class="font-semibold">{{ sectionScore.score }}/{{ sectionScore.max_score }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span>Percentage:</span>
                          <span class="font-semibold">{{ Math.round((sectionScore.score / sectionScore.max_score) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-base-300 rounded-full h-1.5 mt-2">
                          <div class="bg-primary h-1.5 rounded-full" :style="{ width: Math.round((sectionScore.score / sectionScore.max_score) * 100) + '%' }"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Score Data -->
          <div v-else class="mb-6">
            <div class="card bg-base-200">
              <div class="text-center card-body">
                <div class="text-base-content/60">
                  <BarChart3 class="mx-auto mb-2 w-8 h-8 opacity-50" />
                  <p class="font-medium">No score data available</p>
                  <p class="text-sm">Score will be calculated when the response is completed.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="flex gap-2 justify-end pt-4 mt-6 border-t border-base-300">
            <button class="btn btn-sm btn-outline" @click="closeModal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Eye, RefreshCw, FileText, X, BarChart3 } from 'lucide-vue-next'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SurveyStatisticsCard from '@/Components/SurveyStatisticsCard.vue'
import DataTable from '@/Components/DataTable.vue'

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
// Functions
const viewResponse = (response) => {
  selectedResponse.value = response
  document.getElementById('response_modal').showModal()
}

const closeModal = () => {
  selectedResponse.value = null
  document.getElementById('response_modal').close()
}


const refreshData = () => {
  // TODO: Implement refresh functionality
  window.location.reload()
}
</script>