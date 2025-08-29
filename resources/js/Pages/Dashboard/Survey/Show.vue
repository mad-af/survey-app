<template>
  <DashboardLayout :pageTitle="'Survey Management'">
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader title="Survey Details"
        description="View detailed information about surveys including sections, responses, and configurations"
        :showBackButton="true" />

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <span class="loading loading-spinner loading-sm"></span>
        <span class="ml-3 text-sm text-base-content/70">Loading survey details...</span>
      </div>

      <!-- Survey Information Cards -->
      <div v-else-if="selectedSurvey" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Survey Detail Information Card -->
        <div class="space-y-6 lg:col-span-2">
          <SurveyDetailCard :survey="selectedSurvey" />

          <SurveySectionList :sections="surveySections" :survey-id="props.surveyId" :is-loading="isSectionsLoading" />
        </div>

        <!-- Quick Actions and Statistics Cards -->
        <div class="space-y-6">
          <SurveyQuickActions @edit-survey="handleEditSurvey" @manage-survey="handleManageSurvey"
            @view-responses="handleViewResponses" @share-survey="handleShareSurvey" @export-data="handleExportData"
            @delete-survey="handleDeleteSurvey" />

          <SurveyStatistics :statistics="surveyStatistics" />
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="py-12 text-center">
        <div class="flex flex-col gap-4 items-center">
          <div class="text-base-content/60">
            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
              </path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-base-content">Survey Not Found</h3>
          <p class="text-sm text-base-content/60">The survey you're looking for could not be found.</p>
        </div>
      </div>


    </div>

    <!-- Survey Drawer -->
    <SurveyDrawer :is-open="isDrawerOpen" :title="drawerTitle" :survey-data="selectedSurvey" :is-edit-mode="isEditMode"
      @close="closeDrawer" @success="handleDrawerSuccess" />

  </DashboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SurveyDetailCard from '@/Components/SurveyDetailCard.vue'
import SurveyQuickActions from '@/Components/SurveyQuickActions.vue'
import SurveyStatistics from '@/Components/SurveyStatistics.vue'
import SurveySectionList from '@/Components/SurveySectionList.vue'
import SurveyDrawer from '@/Components/SurveyDrawer.vue'

// Props
const props = defineProps({
  surveyId: {
    type: [String, Number],
    required: true
  }
})

// Inject toast function with fallback
const showToast = inject('showToast', (message, type) => {
  console.log(`Toast [${type}]: ${message}`)
  alert(message)
})

// Reactive data
const selectedSurvey = ref(null)
const surveySections = ref([])
const isLoading = ref(false)
const isSectionsLoading = ref(false)
const isDrawerOpen = ref(false)
const drawerTitle = ref('Edit Survey')
const isEditMode = ref(false)

// API Functions
const fetchSurvey = async () => {
  try {
    isLoading.value = true
    const response = await axios.get(`/api/surveys/${props.surveyId}`)

    if (response.data.success) {
      selectedSurvey.value = response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to fetch survey')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch survey'
    showToast(errorMessage, 'error')
    console.error('Error fetching survey:', err)
  } finally {
    isLoading.value = false
  }
}

const fetchSurveySections = async () => {
  try {
    isSectionsLoading.value = true
    const response = await axios.get(`/api/surveys/${props.surveyId}/sections`)

    if (response.data.success) {
      surveySections.value = response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to fetch survey sections')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch survey sections'
    showToast(errorMessage, 'error')
    console.error('Error fetching survey sections:', err)
  } finally {
    isSectionsLoading.value = false
  }
}

const fetchSurveyStatistics = async () => {
  try {
    const response = await axios.get(`/api/surveys/${props.surveyId}/statistics`)

    if (response.data.success) {
      Object.assign(surveyStatistics, response.data.data)
    } else {
      throw new Error(response.data.message || 'Failed to fetch survey statistics')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch survey statistics'
    showToast(errorMessage, 'error')
    console.error('Error fetching survey statistics:', err)
  }
}

const surveyStatistics = reactive({
  totalSections: 0,
  totalQuestions: 0,
  totalResponses: 0,
  completionRate: 0,
  averageTime: 0
})

// Event handlers for quick actions
const handleEditSurvey = () => {
  if (selectedSurvey.value) {
    isEditMode.value = true
    drawerTitle.value = 'Edit Survey'
    isDrawerOpen.value = true
  }
}

const handleManageSurvey = () => {
  // Navigate to manage survey page
  router.visit(`/dashboard/survey/${props.surveyId}/manage`)
}

const handleViewResponses = () => {
  // Navigate to responses page
  router.visit(`/dashboard/survey/${props.surveyId}/response`)
}

const handleShareSurvey = () => {
  console.log('Share survey clicked')
  // Open share modal or copy link
}

const handleExportData = () => {
  console.log('Export data clicked')
  // Export survey data
}

const handleDeleteSurvey = async () => {
  if (!selectedSurvey.value) return

  // Show confirmation dialog
  const confirmed = confirm(`Are you sure you want to delete the survey "${selectedSurvey.value.title}"? This action cannot be undone.`)

  if (confirmed) {
    try {
      const response = await axios.delete(`/api/surveys/${selectedSurvey.value.id}`)

      if (response.data.success) {
        showToast('Survey deleted successfully', 'success')
        // Navigate back to survey index
        router.visit('/dashboard/survey')
      } else {
        throw new Error(response.data.message || 'Failed to delete survey')
      }
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Failed to delete survey'
      showToast(errorMessage, 'error')
      console.error('Error deleting survey:', error)
    }
  }
}

// Drawer event handlers
const closeDrawer = () => {
  isDrawerOpen.value = false
  isEditMode.value = false
}

const handleDrawerSuccess = (updatedSurvey) => {
  // Update the current survey data
  selectedSurvey.value = updatedSurvey
  // Refresh the survey data, sections, and statistics
  fetchSurvey()
  fetchSurveySections()
  fetchSurveyStatistics()
}

// Lifecycle
onMounted(() => {
  fetchSurvey()
  fetchSurveySections()
  fetchSurveyStatistics()
})
</script>