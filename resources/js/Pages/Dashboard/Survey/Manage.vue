<template>
  <DashboardLayout :pageTitle="'Survey Management'">
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader title="Manage Survey"
        description="View detailed information about surveys including sections, responses, and configurations"
        :showBackButton="true" />

      <!-- Survey Information Cards -->
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
        <!-- Survey Detail Information Card -->
        <div class="space-y-3 lg:col-span-3">
          <ManageSurveyCard v-for="section in surveySections" :key="section.id" :section="section"
            @edit-section="handleEditSection" @delete-section="handleDeleteSection" @add-question="handleAddQuestion"
            @edit-question="handleEditQuestion" @delete-question="handleDeleteQuestion" />

          <!-- Empty State -->
          <div v-if="surveySections.length === 0" class="w-full card bg-ghost card-sm">
            <div class="py-8 text-center card-body">
              <div class="flex flex-col gap-2 items-center">
                <div class="text-base-content/40">
                  <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                  </svg>
                </div>
                <h3 class="font-medium text-base-content">No Survey Sections</h3>
                <p class="text-sm text-base-content/60">Start by adding your first survey section</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions and Statistics Cards -->
        <div class="space-y-6">
          <ManageSurveyActionCard />
        </div>
      </div>

    </div>

  </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ManageSurveyActionCard from '@/Components/ManageSurveyActionCard.vue'
import ManageSurveyCard from '@/Components/ManageSurveyCard.vue'

// Sample data - replace with actual data from props or API
const surveySections = ref([
  {
    id: 1,
    title: 'Personal Information',
    description: 'Basic personal details and contact information',
    order: 1,
    questions: [
      {
        id: 1,
        title: 'Full Name',
        description: 'Enter your complete full name',
        type: 'text',
        required: true,
        order: 1
      },
      {
        id: 2,
        title: 'Email Address',
        description: 'Your primary email address for communication',
        type: 'email',
        required: true,
        order: 2
      },
      {
        id: 3,
        title: 'Gender',
        description: 'Please select your gender',
        type: 'select',
        required: false,
        order: 3,
        choices: [
          { id: 1, text: 'Male' },
          { id: 2, text: 'Female' },
          { id: 3, text: 'Other' },
          { id: 4, text: 'Prefer not to say' }
        ]
      }
    ]
  },
  {
    id: 2,
    title: 'Experience & Skills',
    description: 'Professional experience and technical skills assessment',
    order: 2,
    questions: [
      {
        id: 4,
        title: 'Years of Experience',
        description: 'How many years of professional experience do you have?',
        type: 'number',
        required: true,
        order: 1
      },
      {
        id: 5,
        title: 'Programming Languages',
        description: 'Which programming languages are you proficient in? (Select all that apply)',
        type: 'radio',
        required: true,
        order: 2,
        choices: [
          { id: 1, text: 'JavaScript' },
          { id: 2, text: 'Python' },
          { id: 3, text: 'Java' },
          { id: 4, text: 'PHP' },
          { id: 5, text: 'C#' },
          { id: 6, text: 'Go' }
        ]
      }
    ]
  }
])

// Methods
const handleEditSection = (section) => {
  console.log('Edit section:', section)
  // Implement edit logic here
}

const handleDeleteSection = (section) => {
  console.log('Delete section:', section)
  // Implement delete logic here
  if (confirm(`Are you sure you want to delete "${section.title}"?`)) {
    const index = surveySections.value.findIndex(s => s.id === section.id)
    if (index > -1) {
      surveySections.value.splice(index, 1)
    }
  }
}

const handleAddQuestion = (section) => {
  console.log('Add question to section:', section)
  // Implement add question logic here
  const newQuestion = {
    id: Date.now(), // Simple ID generation for demo
    title: 'New Question',
    description: 'Question description',
    type: 'text',
    required: false,
    order: (section.questions?.length || 0) + 1
  }

  const sectionIndex = surveySections.value.findIndex(s => s.id === section.id)
  if (sectionIndex > -1) {
    if (!surveySections.value[sectionIndex].questions) {
      surveySections.value[sectionIndex].questions = []
    }
    surveySections.value[sectionIndex].questions.push(newQuestion)
  }
}

const handleEditQuestion = (question, section) => {
  console.log('Edit question:', question, 'in section:', section)
  // Implement edit question logic here
}

const handleDeleteQuestion = (question, section) => {
  console.log('Delete question:', question, 'from section:', section)
  // Implement delete question logic here
  if (confirm(`Are you sure you want to delete "${question.title}"?`)) {
    const sectionIndex = surveySections.value.findIndex(s => s.id === section.id)
    if (sectionIndex > -1) {
      const questionIndex = surveySections.value[sectionIndex].questions.findIndex(q => q.id === question.id)
      if (questionIndex > -1) {
        surveySections.value[sectionIndex].questions.splice(questionIndex, 1)
      }
    }
  }
}
</script>