<template>
  <Head title="Survey Questions" />
  
  <ProsesSurveyLayout>
    <!-- Survey Navbar with Integrated Tabs -->
    <SurveyNavbar 
      :survey-title="surveyTitle"
      :section-description="displaySections.find(s => s.id === currentSectionId)?.description"
      :current-section="currentSection"
      :total-sections="totalSections"
      :show-progress="showProgress"
      :sections="displaySections"
      :current-section-id="currentSectionId"
      @section-change="handleSectionChange"
    />

    <!-- Survey Questions Cards -->
    <div v-if="currentQuestions.length > 0" class="space-y-4">
      <SurveyQuestionCard 
        v-for="(question, index) in currentQuestions" 
        :key="question.id"
        :question="question"
        :question-number="index + 1"
      />
    </div>

    <!-- Empty State -->
    <SurveyEmptyState v-else />

    <!-- Navigation Buttons -->
    <SurveyNavigation 
      :answered-questions="answeredQuestions"
      :total-questions="totalQuestions"
      :has-previous-section="hasPreviousSection"
      :has-next-section="hasNextSection"
      :can-proceed="canProceed"
      @previous-section="handlePreviousSection"
      @next-section="handleNextSection"
    />
  </ProsesSurveyLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import ProsesSurveyLayout from '@/Layouts/ProsesSurveyLayout.vue'
import SurveyNavbar from '@/Components/SurveyNavbar.vue'
import SurveyQuestionCard from '@/Components/SurveyQuestionCard.vue'
import SurveyEmptyState from '@/Components/SurveyEmptyState.vue'
import SurveyNavigation from '@/Components/SurveyNavigation.vue'

// Survey data
const surveyTitle = ref('Survey Kepuasan Layanan')
const currentSectionId = ref(1)
const currentSection = ref(1)
const totalSections = ref(3)
const showProgress = ref(true)
const answeredQuestions = ref(0)
const hasPreviousSection = ref(false)
const hasNextSection = ref(true)
const canProceed = ref(true)

// Dummy data for sections
const dummySections = ref([
  {
    id: 1,
    survey_id: 1,
    title: 'Informasi Umum',
    description: 'Bagian untuk mengumpulkan informasi dasar responden',
    order: 1
  },
  {
    id: 2,
    survey_id: 1,
    title: 'Kepuasan Layanan',
    description: 'Evaluasi tingkat kepuasan terhadap layanan yang diberikan',
    order: 2
  },
  {
    id: 3,
    survey_id: 1,
    title: 'Saran & Masukan',
    description: 'Bagian untuk memberikan saran dan masukan perbaikan',
    order: 3
  }
])

// Dummy data for questions
const dummyQuestions = ref([
  {
    id: 1,
    section_id: 1,
    text: 'Berapa usia Anda saat ini?',
    type: 'number',
    required: true,
    order: 1,
    score_weight: 1.0,
    choices: []
  },
  {
    id: 2,
    section_id: 1,
    text: 'Ceritakan pengalaman Anda menggunakan layanan kami',
    type: 'long_text',
    required: true,
    order: 2,
    score_weight: 2.0,
    choices: []
  },
  {
    id: 3,
    section_id: 1,
    text: 'Bagaimana tingkat kepuasan Anda terhadap layanan kami?',
    type: 'single_choice',
    required: true,
    order: 3,
    score_weight: 3.0,
    choices: [
      { id: 1, question_id: 3, text: 'Sangat Tidak Puas', order: 1 },
      { id: 2, question_id: 3, text: 'Tidak Puas', order: 2 },
      { id: 3, question_id: 3, text: 'Netral', order: 3 },
      { id: 4, question_id: 3, text: 'Puas', order: 4 },
      { id: 5, question_id: 3, text: 'Sangat Puas', order: 5 }
    ]
  }
])

// Computed properties
const displaySections = computed(() => {
  return dummySections.value
})

const displayQuestions = computed(() => {
  return dummyQuestions.value
})

const currentQuestions = computed(() => {
  if (!currentSectionId.value) return []
  return displayQuestions.value.filter(question => question.section_id === currentSectionId.value)
})

const totalQuestions = computed(() => {
  return currentQuestions.value.length
})

// Event handlers
const handleSectionChange = (sectionId) => {
  currentSectionId.value = sectionId
  currentSection.value = displaySections.value.findIndex(s => s.id === sectionId) + 1
  
  hasPreviousSection.value = currentSection.value > 1
  hasNextSection.value = currentSection.value < totalSections.value
}

const handlePreviousSection = () => {
  if (hasPreviousSection.value) {
    const prevSection = currentSection.value - 1
    const prevSectionData = displaySections.value[prevSection - 1]
    handleSectionChange(prevSectionData.id)
  }
}

const handleNextSection = () => {
  if (hasNextSection.value) {
    const nextSection = currentSection.value + 1
    const nextSectionData = displaySections.value[nextSection - 1]
    handleSectionChange(nextSectionData.id)
  }
}
</script>