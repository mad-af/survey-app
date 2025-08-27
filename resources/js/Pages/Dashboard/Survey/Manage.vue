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
          <div v-if="surveySections.length === 0" class="w-full card bg-ghost card-sm">
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
      :available-sections="surveySections" title="Add New Question" @close="closeQuestionDrawer" @success="handleQuestionSuccess" />

    <!-- Edit Question Drawer -->
    <QuestionDrawer :is-open="questionDrawer.isEditOpen" :is-edit-mode="true" :question-data="questionDrawer.editData"
      :section-id="questionDrawer.sectionId" :available-sections="surveySections" title="Edit Question" @close="closeQuestionDrawer"
      @success="handleQuestionSuccess" />
  </DashboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import ManageSurveyActionCard from '@/Components/ManageSurveyActionCard.vue'
import ManageSurveyCard from '@/Components/ManageSurveyCard.vue'
import SectionDrawer from '@/Components/SectionDrawer.vue'
import QuestionDrawer from '@/Components/QuestionDrawer.vue'
import { ListOrdered } from 'lucide-vue-next'

// ===== PROPS & CONSTANTS =====
const props = defineProps({
  surveyId: {
    type: [String, Number],
    required: true
  }
})

const currentSurveyId = ref(props.surveyId)

// Data from API
const surveySections = ref([])

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
  // Refresh the sections list to get updated questions
  await fetchSections()

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
      await axios.delete(`/api/surveys/${currentSurveyId.value}/sections/${section.id}`)
      
      // Refresh sections list
      await fetchSections()
      
      console.log('Section deleted successfully')
    } catch (error) {
      console.error('Error deleting section:', error)
      alert('Error deleting section. Please try again.')
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
  if (confirm(`Are you sure you want to delete "${question.text}"?`)) {
    try {
      await axios.delete(`/api/sections/${section.id}/questions/${question.id}`)
      
      // Refresh sections list to get updated questions
      await fetchSections()
      
      console.log('Question deleted successfully')
    } catch (error) {
      console.error('Error deleting question:', error)
      alert('Error deleting question. Please try again.')
    }
  }
}

// ===== API METHODS =====
const fetchSections = async () => {
  try {
    const response = await axios.get(`/api/surveys/${currentSurveyId.value}/sections`)
    surveySections.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching sections:', error)
    surveySections.value = []
  }
}

// ===== LIFECYCLE HOOKS =====
onMounted(() => {
  fetchSections()
})
</script>