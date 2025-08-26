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
        <div class="lg:col-span-2 space-y-6">
          <SurveyDetailCard :survey="selectedSurvey" />

          <SurveySectionList 
            :sections="selectedSurvey.sections"
            @add-section="handleAddSection"
            @edit-section="handleEditSection"
            @view-questions="handleViewQuestions"
            @delete-section="handleDeleteSection"
          />
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
import SurveySectionList from '@/Components/SurveySectionList.vue'

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
  sections: [
    {
      id: 1,
      survey_id: 1,
      title: 'Personal Information',
      description: 'Basic demographic and contact information',
      order: 1,
      questions: [
        { id: 1, title: 'Full Name', type: 'text' },
        { id: 2, title: 'Email Address', type: 'email' },
        { id: 3, title: 'Age Group', type: 'select' }
      ]
    },
    {
      id: 2,
      survey_id: 1,
      title: 'Product Experience',
      description: 'Questions about your experience with our products',
      order: 2,
      questions: [
        { id: 4, title: 'Overall Satisfaction', type: 'rating' },
        { id: 5, title: 'Product Quality', type: 'rating' },
        { id: 6, title: 'Value for Money', type: 'rating' },
        { id: 7, title: 'Additional Comments', type: 'textarea' }
      ]
    },
    {
      id: 3,
      survey_id: 1,
      title: 'Service Feedback',
      description: 'Feedback about our customer service and support',
      order: 3,
      questions: [
        { id: 8, title: 'Service Quality', type: 'rating' },
        { id: 9, title: 'Response Time', type: 'rating' },
        { id: 10, title: 'Staff Helpfulness', type: 'rating' }
      ]
    }
  ],
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

// Event handlers for section actions
const handleAddSection = () => {
  console.log('Add section clicked')
  // Navigate to add section page or open add section modal
}

const handleEditSection = (section) => {
  console.log('Edit section clicked:', section)
  // Navigate to edit section page or open edit section modal
}

const handleViewQuestions = (section) => {
  console.log('View questions clicked:', section)
  // Navigate to questions page or open questions modal
}

const handleDeleteSection = (section) => {
  console.log('Delete section clicked:', section)
  // Show confirmation modal and delete section
}
</script>