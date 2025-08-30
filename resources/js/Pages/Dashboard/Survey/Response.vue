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
        :survey-code="survey?.code"
      />
      
      <!-- Additional Statistics -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">Total Responses</div>
          <div class="stat-value text-primary">{{ totalResponses }}</div>
          <div class="stat-desc">All survey responses</div>
        </div>
        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">Completed</div>
          <div class="stat-value text-success">{{ completedResponses }}</div>
          <div class="stat-desc">{{ completionRate }}% completion rate</div>
        </div>
        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">In Progress</div>
          <div class="stat-value text-warning">{{ inProgressResponses + startedResponses }}</div>
          <div class="stat-desc">Currently being filled</div>
        </div>
        <div class="stat bg-base-200 rounded-lg">
          <div class="stat-title">Abandoned</div>
          <div class="stat-value text-error">{{ abandonedResponses }}</div>
          <div class="stat-desc">Left incomplete</div>
        </div>
      </div>

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
                  class="input input-bordered input-sm w-full md:w-64"
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
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
              </button>
              <button 
                class="btn btn-sm btn-outline btn-success"
                @click="exportResponses"
                title="Export Responses"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
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
              <svg class="mx-auto mb-4 w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
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
        <div class="max-w-6xl modal-box">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Response Details - #{{ selectedResponse.id }}</h3>
            <button class="btn btn-sm btn-circle btn-ghost" @click="selectedResponse = null">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- Response Status & Progress -->
          <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Response Status</h4>
                <div class="space-y-2 text-sm">
                  <div><strong>Status:</strong> 
                    <span class="ml-2 badge" :class="getStatusBadgeClass(selectedResponse.status)">
                      {{ getStatusLabel(selectedResponse.status) }}
                    </span>
                  </div>
                  <div><strong>Progress:</strong> 
                    <span class="ml-2 badge" :class="getStepBadgeClass(selectedResponse.current_step)">
                      {{ getStepLabel(selectedResponse.current_step) }}
                    </span>
                  </div>
                  <div><strong>Token:</strong> {{ selectedResponse.respondent_token || '-' }}</div>
                </div>
              </div>
            </div>
            
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Timeline</h4>
                <div class="space-y-2 text-sm">
                  <div><strong>Started:</strong> {{ formatDate(selectedResponse.started_at) }}</div>
                  <div><strong>Last Updated:</strong> {{ formatDate(selectedResponse.updated_at) }}</div>
                  <div><strong>Submitted:</strong> {{ formatDate(selectedResponse.submitted_at) }}</div>
                  <div><strong>Duration:</strong> 
                    {{ selectedResponse.status === 'completed' 
                       ? formatDuration(selectedResponse.started_at, selectedResponse.submitted_at)
                       : formatDuration(selectedResponse.started_at, selectedResponse.updated_at) }}
                  </div>
                </div>
              </div>
            </div>
            
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Meta Information</h4>
                <div class="space-y-2 text-sm">
                  <div v-if="selectedResponse.meta">
                    <pre class="text-xs bg-base-300 p-2 rounded overflow-auto max-h-20">{{ JSON.stringify(selectedResponse.meta, null, 2) }}</pre>
                  </div>
                  <div v-else class="text-base-content/60">No meta data</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Respondent Info -->
          <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Respondent Information</h4>
                <div class="space-y-2 text-sm">
                  <div><strong>ID:</strong> {{ selectedResponse.respondent?.id || '-' }}</div>
                  <div><strong>External ID:</strong> {{ selectedResponse.respondent?.external_id || '-' }}</div>
                  <div><strong>Name:</strong> {{ selectedResponse.respondent?.name || 'Anonymous' }}</div>
                  <div><strong>Email:</strong> {{ selectedResponse.respondent?.email || '-' }}</div>
                  <div><strong>Phone:</strong> {{ selectedResponse.respondent?.phone || '-' }}</div>
                  <div><strong>Gender:</strong> {{ selectedResponse.respondent?.gender || '-' }}</div>
                  <div><strong>Birth Year:</strong> {{ selectedResponse.respondent?.birth_year || '-' }}</div>
                  <div><strong>Consent At:</strong> {{ formatDate(selectedResponse.respondent?.consent_at) }}</div>
                </div>
              </div>
            </div>
            
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Organization Details</h4>
                <div class="space-y-2 text-sm">
                  <div><strong>Organization:</strong> {{ selectedResponse.respondent?.organization || '-' }}</div>
                  <div><strong>Department:</strong> {{ selectedResponse.respondent?.department || '-' }}</div>
                  <div><strong>Role:</strong> {{ selectedResponse.respondent?.role_title || '-' }}</div>
                  <div><strong>Location:</strong> {{ selectedResponse.respondent?.location || '-' }}</div>
                  <div v-if="selectedResponse.respondent?.demographics">
                    <strong>Demographics:</strong>
                    <pre class="text-xs bg-base-300 p-2 rounded overflow-auto max-h-20 mt-1">{{ JSON.stringify(selectedResponse.respondent.demographics, null, 2) }}</pre>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Score Information -->
          <div v-if="selectedResponse.score" class="mb-6">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Score Information</h4>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div class="space-y-2 text-sm">
                    <div><strong>Total Score:</strong> {{ selectedResponse.score.total_score }}/{{ selectedResponse.score.max_possible_score }}</div>
                    <div><strong>Percentage:</strong> {{ selectedResponse.score.percentage }}%</div>
                    <div v-if="selectedResponse.score.result_category">
                      <strong>Category:</strong> 
                      <span class="ml-2 badge" :class="getCategoryBadgeClass(selectedResponse.score.result_category.color)">
                        {{ selectedResponse.score.result_category.name }}
                      </span>
                    </div>
                  </div>
                  <div class="space-y-2 text-sm">
                    <div v-if="selectedResponse.score.result_category?.description">
                      <strong>Category Description:</strong>
                      <p class="mt-1 text-xs bg-base-300 p-2 rounded">{{ selectedResponse.score.result_category.description }}</p>
                    </div>
                    <div v-if="selectedResponse.score.result_category">
                      <strong>Score Range:</strong> {{ selectedResponse.score.result_category.min_score }} - {{ selectedResponse.score.result_category.max_score }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section Scores -->
          <div v-if="selectedResponse.score?.section_scores" class="mb-6">
            <h4 class="mb-3 font-semibold">Section Scores</h4>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
              <div v-for="sectionScore in selectedResponse.score.section_scores" :key="sectionScore.section_id" class="card bg-base-200">
                <div class="card-body">
                  <div class="flex justify-between items-start mb-2">
                    <div class="text-sm font-medium">{{ getSectionTitle(sectionScore.section_id) }}</div>
                    <div class="text-xs text-base-content/60">#{{ sectionScore.section_id }}</div>
                  </div>
                  <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                      <span>Score:</span>
                      <span>{{ sectionScore.score }}/{{ sectionScore.max_score }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span>Percentage:</span>
                      <span>{{ sectionScore.percentage }}%</span>
                    </div>
                    <div class="w-full bg-base-300 rounded-full h-2 mt-2">
                      <div class="bg-primary h-2 rounded-full" :style="{ width: sectionScore.percentage + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Score Data -->
          <div v-else class="mb-6">
            <div class="card bg-base-200">
              <div class="card-body text-center">
                <div class="text-base-content/60">
                  <svg class="mx-auto mb-2 w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                  <p class="font-medium">No score data available</p>
                  <p class="text-sm">Score will be calculated when the response is completed.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-base-300">
            <button class="btn btn-sm btn-outline" @click="closeModal">
              Close
            </button>
            <button class="btn btn-sm btn-primary" @click="exportResponses">
              Export This Response
            </button>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Eye } from 'lucide-vue-next'
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
    key: 'organization',
    label: 'Organization',
    formatter: (value, item) => {
      const org = item.respondent?.organization || '-'
      const dept = item.respondent?.department || '-'
      return dept !== '-' ? `${org} - ${dept}` : org
    }
  },
  {
    key: 'respondent.role_title',
    label: 'Role',
    formatter: (value, item) => item.respondent?.role_title || '-'
  },
  {
    key: 'respondent.gender',
    label: 'Gender',
    formatter: (value, item) => item.respondent?.gender || '-'
  },
  {
    key: 'respondent.birth_year',
    label: 'Birth Year',
    formatter: (value, item) => item.respondent?.birth_year || '-'
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
    key: 'category',
    label: 'Category',
    type: 'badge',
    formatter: (value, item) => item.score?.result_category?.name || '-',
    class: (value, item) => item.score?.result_category ? getCategoryBadgeClass(item.score.result_category.color) : 'badge-ghost'
  },
  {
    key: 'started_at',
    label: 'Started',
    type: 'date',
    formatter: (value) => formatDate(value)
  },
  {
    key: 'respondent_token',
    label: 'Token',
    formatter: (value) => value ? value.substring(0, 8) + '...' : '-'
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

const exportResponses = () => {
  // TODO: Implement export functionality
  console.log('Export responses functionality to be implemented')
}

const refreshData = () => {
  // TODO: Implement refresh functionality
  window.location.reload()
}
</script>