<template>
  <DashboardLayout :pageTitle="'Survey Management'">
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader title="Manage Survey"
        description="Manage your survey sections and questions, add or modify content, and organize your survey structure"
        :showBackButton="true" />

      <!-- Survey Information Cards -->
      <div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
        <!-- Survey Detail Information Card -->
        <div class="space-y-3 lg:col-span-3">
          <ManageSurveyCard v-for="section in surveySections" :key="section.id" :section="section"
            @edit-section="handleEditSection" @delete-section="handleDeleteSection" @add-question="handleAddQuestion"
            @edit-question="handleEditQuestion" @delete-question="handleDeleteQuestion" />

          <!-- Empty State -->
          <div v-if="surveySections.length > 0" class="w-full card bg-ghost card-sm">
            <div class="py-8 text-center card-body">
              <div class="flex flex-col gap-2 items-center">
                <div class="text-base-content/80">
                  <ListOrdered :stroke-width="1.5" class="size-12" />
                </div>
                <h3 class="font-medium text-base-content">No Survey Sections</h3>
                <p class="text-sm text-base-content/60">Start by adding your first survey section</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions and Statistics Cards -->
        <div class="space-y-6">
          <ManageSurveyActionCard @add-section="openAddSectionDrawer" @add-question="openAddQuestionDrawer" />
        </div>
      </div>
    </div>

    <!-- Section Drawers -->
    <!-- Add Section Drawer -->
    <SectionDrawer :is-open="sectionDrawer.isAddOpen" :is-edit-mode="false" :survey-id="currentSurveyId"
      title="Add New Section" @close="closeSectionDrawer" @success="handleSectionSuccess" />

    <!-- Edit Section Drawer -->
    <SectionDrawer :is-open="sectionDrawer.isEditOpen" :is-edit-mode="true" :section-data="sectionDrawer.editData"
      :survey-id="currentSurveyId" title="Edit Section" @close="closeSectionDrawer" @success="handleSectionSuccess" />

    <!-- Question Drawers -->
    <!-- Add Question Drawer -->
    <QuestionDrawer :is-open="questionDrawer.isAddOpen" :is-edit-mode="false" :section-id="questionDrawer.sectionId"
      title="Add New Question" @close="closeQuestionDrawer" @success="handleQuestionSuccess" />

    <!-- Edit Question Drawer -->
    <QuestionDrawer :is-open="questionDrawer.isEditOpen" :is-edit-mode="true" :question-data="questionDrawer.editData"
      :section-id="questionDrawer.sectionId" title="Edit Question" @close="closeQuestionDrawer"
      @success="handleQuestionSuccess" />
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ManageSurveyActionCard from '@/Components/ManageSurveyActionCard.vue'
import ManageSurveyCard from '@/Components/ManageSurveyCard.vue'
import SectionDrawer from '@/Components/SectionDrawer.vue'
import QuestionDrawer from '@/Components/QuestionDrawer.vue'
import { ListOrdered } from 'lucide-vue-next'

// ===== PROPS & CONSTANTS =====
const currentSurveyId = ref(1) // This should come from route params or props

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

// ===== DRAWER STATE MANAGEMENT =====
// Section Drawer State
const sectionDrawer = reactive({
  isAddOpen: false,
  isEditOpen: false,
  editData: null
})

// Question Drawer State
const questionDrawer = reactive({
  isAddOpen: false,
  isEditOpen: false,
  editData: null,
  sectionId: null
})

// ===== DRAWER METHODS =====
// Section Drawer Methods
const openAddSectionDrawer = () => {
  sectionDrawer.isAddOpen = true
}

const openEditSectionDrawer = (sectionData) => {
  sectionDrawer.editData = sectionData
  sectionDrawer.isEditOpen = true
}

const closeSectionDrawer = () => {
  sectionDrawer.isAddOpen = false
  sectionDrawer.isEditOpen = false
  sectionDrawer.editData = null
}

const handleSectionSuccess = async (sectionData) => {
  // Refresh the sections list
  await fetchSections()

  // Close drawer
  closeSectionDrawer()
}

// Question Drawer Methods
const openAddQuestionDrawer = (sectionId = null) => {
  questionDrawer.sectionId = sectionId
  questionDrawer.isAddOpen = true
}

const openEditQuestionDrawer = (questionData, sectionId) => {
  questionDrawer.editData = questionData
  questionDrawer.sectionId = sectionId
  questionDrawer.isEditOpen = true
}

const closeQuestionDrawer = () => {
  questionDrawer.isAddOpen = false
  questionDrawer.isEditOpen = false
  questionDrawer.editData = null
  questionDrawer.sectionId = null
}

const handleQuestionSuccess = async (questionData) => {
  // Refresh the questions list
  await fetchQuestions()

  // Close drawer
  closeQuestionDrawer()
}

// ===== EVENT HANDLERS =====
// Section Event Handlers
const handleEditSection = (section) => {
  openEditSectionDrawer(section)
}

const handleDeleteSection = async (section) => {
  if (confirm(`Are you sure you want to delete "${section.title}"?`)) {
    try {
      // TODO: Replace with actual API call
      // await axios.delete(`/api/sections/${section.id}`)

      // For now, remove from local state
      const index = surveySections.value.findIndex(s => s.id === section.id)
      if (index > -1) {
        surveySections.value.splice(index, 1)
      }

      // TODO: Show success toast
      console.log('Section deleted successfully')
    } catch (error) {
      console.error('Error deleting section:', error)
      // TODO: Show error toast
    }
  }
}

// Question Event Handlers
const handleAddQuestion = (section) => {
  openAddQuestionDrawer(section.id)
}

const handleEditQuestion = (question, section) => {
  openEditQuestionDrawer(question, section.id)
}

const handleDeleteQuestion = async (question, section) => {
  if (confirm(`Are you sure you want to delete "${question.title}"?`)) {
    try {
      // TODO: Replace with actual API call
      // await axios.delete(`/api/questions/${question.id}`)

      // For now, remove from local state
      const sectionIndex = surveySections.value.findIndex(s => s.id === section.id)
      if (sectionIndex > -1) {
        const questionIndex = surveySections.value[sectionIndex].questions.findIndex(q => q.id === question.id)
        if (questionIndex > -1) {
          surveySections.value[sectionIndex].questions.splice(questionIndex, 1)
        }
      }

      // TODO: Show success toast
      console.log('Question deleted successfully')
    } catch (error) {
      console.error('Error deleting question:', error)
      // TODO: Show error toast
    }
  }
}

// ===== API METHODS =====
// TODO: Implement actual API calls
const fetchSections = async () => {
  // Placeholder for API call to refresh sections
  console.log('Fetching sections...')
}

const fetchQuestions = async () => {
  // Placeholder for API call to refresh questions
  console.log('Fetching questions...')
}
</script>