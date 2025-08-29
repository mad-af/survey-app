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

      <!-- Responses Table -->
      <div class="border shadow-sm card bg-base-100 border-base-200">
        <div class="card-body">
          <div class="mb-4 card-title">
            <h3 class="text-lg font-semibold">Response Details</h3>
          </div>
          
          <DataTable
            v-if="responses.length > 0"
            :data="responses"
            :columns="tableColumns"
            :actions="tableActions"
            :items-per-page="10"
            @view-response="viewResponse"
          />
          
          <div v-else class="py-8 text-center">
            <div class="text-base-content/60">
              <svg class="mx-auto mb-4 w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <p class="mb-2 text-lg font-medium">No responses yet</p>
              <p class="text-sm">Responses will appear here once participants start submitting the survey.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Response Detail Modal -->
      <div v-if="selectedResponse" class="modal modal-open">
        <div class="max-w-4xl modal-box">
          <h3 class="mb-4 text-lg font-bold">Response Details</h3>
          
          <!-- Respondent Info -->
          <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2">
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Respondent Information</h4>
                <div class="space-y-2 text-sm">
                  <div><strong>Name:</strong> {{ selectedResponse.respondent?.name || 'Anonymous' }}</div>
                  <div><strong>Email:</strong> {{ selectedResponse.respondent?.email || '-' }}</div>
                  <div><strong>Phone:</strong> {{ selectedResponse.respondent?.phone || '-' }}</div>
                  <div><strong>Gender:</strong> {{ selectedResponse.respondent?.gender || '-' }}</div>
                  <div><strong>Birth Year:</strong> {{ selectedResponse.respondent?.birth_year || '-' }}</div>
                  <div><strong>Organization:</strong> {{ selectedResponse.respondent?.organization || '-' }}</div>
                  <div><strong>Department:</strong> {{ selectedResponse.respondent?.department || '-' }}</div>
                  <div><strong>Role:</strong> {{ selectedResponse.respondent?.role_title || '-' }}</div>
                  <div><strong>Location:</strong> {{ selectedResponse.respondent?.location || '-' }}</div>
                </div>
              </div>
            </div>
            
            <div class="card bg-base-200">
              <div class="card-body">
                <h4 class="text-base card-title">Score Information</h4>
                <div v-if="selectedResponse.score" class="space-y-2 text-sm">
                  <div><strong>Total Score:</strong> {{ selectedResponse.score.total_score }}/{{ selectedResponse.score.max_possible_score }}</div>
                  <div><strong>Percentage:</strong> {{ selectedResponse.score.percentage }}%</div>
                  <div v-if="selectedResponse.score.category">
                    <strong>Category:</strong> 
                    <span class="ml-2 badge" :class="getCategoryBadgeClass(selectedResponse.score.category.color)">
                      {{ selectedResponse.score.category.name }}
                    </span>
                  </div>
                  <div v-if="selectedResponse.score.category?.description" class="mt-2">
                    <strong>Description:</strong> {{ selectedResponse.score.category.description }}
                  </div>
                </div>
                <div v-else class="text-base-content/60">
                  No score data available
                </div>
              </div>
            </div>
          </div>

          <!-- Section Scores -->
          <div v-if="selectedResponse.score?.section_scores" class="mb-6">
            <h4 class="mb-3 font-semibold">Section Scores</h4>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
              <div v-for="sectionScore in selectedResponse.score.section_scores" :key="sectionScore.section_id" class="card bg-base-200">
                <div class="py-3 card-body">
                  <div class="flex justify-between items-center">
                    <div class="text-sm font-medium">{{ getSectionTitle(sectionScore.section_id) }}</div>
                    <div class="text-sm">{{ sectionScore.score }}/{{ sectionScore.max_score }} ({{ sectionScore.percentage }}%)</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-action">
            <button class="btn" @click="selectedResponse = null">Close</button>
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
  totalResponses: {
    type: Number,
    default: 0
  },
  completedResponses: {
    type: Number,
    default: 0
  },
  inProgressResponses: {
    type: Number,
    default: 0
  }
})

// Reactive data
const selectedResponse = ref(null)

// Table configuration
const tableColumns = computed(() => [
  {
    key: 'respondent.name',
    label: 'Respondent',
    formatter: (value, item) => item.respondent?.name || 'Anonymous'
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
    formatter: (value, item) => item.score?.category?.name || '-',
    class: (value, item) => item.score?.category ? getCategoryBadgeClass(item.score.category.color) : 'badge-ghost'
  },
  {
    key: 'submitted_at',
    label: 'Submitted',
    type: 'date',
    formatter: (value, item) => formatDate(value || item.created_at)
  }
])

const tableActions = computed(() => [
  {
    name: 'view',
    label: 'View Details',
    icon: Eye,
    event: 'view-response',
    class: ''
  }
])

// Methods
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'completed':
      return 'badge-success'
    case 'in_progress':
      return 'badge-warning'
    case 'draft':
      return 'badge-info'
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
    case 'draft':
      return 'Draft'
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
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getSectionTitle = (sectionId) => {
  if (!props.survey?.sections) return `Section ${sectionId}`
  const section = props.survey.sections.find(s => s.id === sectionId)
  return section ? section.title : `Section ${sectionId}`
}

const viewResponse = (response) => {
  selectedResponse.value = response
}
</script>