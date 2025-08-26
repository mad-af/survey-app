<template>
  <DashboardLayout 
    :pageTitle="'Survey Management'"
  >
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader 
        title="Survey Details" 
        description="View detailed information about surveys including sections, responses, and configurations"
        :showBackButton="true"
      />

      <!-- Survey Information Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Survey Detail Information Card -->
        <div class="lg:col-span-2">
          <SurveyDetailCard :survey="selectedSurvey" />
        </div>
        
        <!-- Quick Actions and Statistics Cards -->
        <div class="space-y-6">
          <SurveyQuickActions 
            @edit-survey="handleEditSurvey"
            @view-responses="handleViewResponses"
            @share-survey="handleShareSurvey"
            @export-data="handleExportData"
            @delete-survey="handleDeleteSurvey"
          />
          
          <SurveyStatistics :statistics="surveyStatistics" />
        </div>
      </div>

      

    </div>

  </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SurveyDetailCard from '@/Components/SurveyDetailCard.vue'
import SurveyQuickActions from '@/Components/SurveyQuickActions.vue'
import SurveyStatistics from '@/Components/SurveyStatistics.vue'

// Sample data - replace with actual data from API
const selectedSurvey = ref({
  id: 1,
  title: 'Customer Satisfaction Survey 2024',
  code: 'SRV-2024-001',
  description: 'This comprehensive survey aims to gather valuable feedback from our customers about their experience with our products and services. Your responses will help us improve and better serve you in the future.',
  status: 'active',
  visibility: 'public',
  is_anonymous: false,
  starts_at: '2024-01-15T09:00:00Z',
  ends_at: '2024-01-31T23:59:00Z',
  owner: {
    id: 1,
    name: 'John Doe',
    email: 'john.doe@example.com'
  },
  sections: [],
  responses: [],
  respondents: []
})

const surveyStatistics = reactive({
  totalSections: 5,
  totalQuestions: 24,
  totalResponses: 156,
  completionRate: 87.5,
  averageTime: 12.3
})

// Event handlers for quick actions
const handleEditSurvey = () => {
  console.log('Edit survey clicked')
  // Navigate to edit page or open edit modal
}

const handleViewResponses = () => {
  console.log('View responses clicked')
  // Navigate to responses page
}

const handleShareSurvey = () => {
  console.log('Share survey clicked')
  // Open share modal or copy link
}

const handleExportData = () => {
  console.log('Export data clicked')
  // Export survey data
}

const handleDeleteSurvey = () => {
  console.log('Delete survey clicked')
  // Show confirmation modal and delete survey
}
</script>