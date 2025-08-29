<template>

  <Head title="Survey Questions" />

  <ProsesSurveyLayout>
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-64">
      <div class="text-center">
        <div class="mx-auto mb-4 w-12 h-12 rounded-full border-b-2 animate-spin border-primary"></div>
        <p class="text-base-content/70">Memuat data survey...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex justify-center items-center min-h-64">
      <div class="text-center">
        <div class="mb-4 text-6xl">⚠️</div>
        <h3 class="mb-2 text-lg font-semibold text-base-content/70">Gagal Memuat Survey</h3>
        <p class="mb-4 text-base-content/70">{{ error }}</p>
        <Link :href="route('entry')" class="link link-primary">Kembali ke-halaman entry token</Link>
      </div>
    </div>

    <!-- Survey Content -->
    <template v-else-if="surveyData">

      <!-- Survey Navbar with Integrated Tabs -->
      <SurveyNavbar :survey-title="surveyTitle"
        :section-description="displaySections.find(s => s.id === currentSectionId)?.description"
        :current-section="currentSection" :total-sections="totalSections" :show-progress="showProgress"
        :sections="displaySections" :current-section-id="currentSectionId" @section-change="handleSectionChange" />

      <!-- Survey Questions Cards -->
      <div class="space-y-4">
        <SurveyQuestionCard v-for="(question, index) in currentQuestions" :key="question.id" :question="question"
          :question-number="index + 1" :model-value="getValueAnswer(answers[question.id], question.type)"
          @update:model-value="handleAnswerChange(question.id, $event)" />
      </div>


      <!-- Navigation Buttons -->
      <SurveyNavigation :answered-questions="answeredQuestions" :total-questions="totalQuestions"
        :has-previous-section="hasPreviousSection" :has-next-section="hasNextSection" :can-proceed="canProceed"
        @previous-section="handlePreviousSection" @next-section="handleNextSection" />
    </template>
  </ProsesSurveyLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import ProsesSurveyLayout from '@/Layouts/ProsesSurveyLayout.vue'
import SurveyNavbar from '@/Components/SurveyNavbar.vue'
import SurveyQuestionCard from '@/Components/SurveyQuestionCard.vue'
import SurveyNavigation from '@/Components/SurveyNavigation.vue'

// Route helper
const route = (name) => {
  const routes = {
    'entry': '/entry',
  }
  return routes[name] || '/'
}

// Props
const props = defineProps({
  surveyCode: {
    type: String,
    required: true
  },
  surveyData: {
    type: Object,
    default: null
  },
  existingResponse: {
    type: Object,
    default: null
  }
})

// Survey data from SSR
const surveyData = ref(props.surveyData)
const currentSectionId = ref(null)
const loading = ref(false)
const error = ref(null)

// Survey answers
const answers = ref({})

// Response tracking
const responseId = ref(null)
const respondentToken = ref(null)
const hasExistingData = ref(false)

// Initialize data from SSR props
const initializeData = () => {
  // Set survey data from props
  if (props.surveyData) {
    surveyData.value = props.surveyData

    // Set initial section
    if (props.surveyData.sections && props.surveyData.sections.length > 0) {
      currentSectionId.value = props.surveyData.sections[0].id
    }
  }

  // Load existing response data if available
  if (props.existingResponse) {
    answers.value = props.existingResponse.answers || {}
    responseId.value = props.existingResponse.id
    hasExistingData.value = true
    console.log('Loaded existing response from SSR:', props.existingResponse)
  }

}

const getValueAnswer = (answer, type) => {
  switch (type) {
    case 'short_text':
    case 'long_text':
    case 'number':
      return answer.answer_value
    case 'single_choice':
      return answer.choice_id
    case 'multiple_choice':
      return JSON.parse(answer.value_json)
    default:
      return answer
  }
}

// Initialize data on component mount
onMounted(() => {
  initializeData()
})

// Computed properties
const surveyTitle = computed(() => {
  return surveyData.value?.title || 'Survey'
})

const displaySections = computed(() => {
  return surveyData.value?.sections || []
})

const displayQuestions = computed(() => {
  const allQuestions = []
  displaySections.value.forEach(section => {
    if (section.questions) {
      allQuestions.push(...section.questions)
    }
  })
  return allQuestions
})

const currentQuestions = computed(() => {
  const currentSec = displaySections.value.find(s => s.id === currentSectionId.value)
  return currentSec?.questions || []
})

const answeredQuestions = computed(() => {
  return Object.keys(answers.value).length
})

const totalQuestions = computed(() => {
  return displayQuestions.value.length
})

const canProceed = computed(() => {
  // Check if all questions in current section are answered
  return currentQuestions.value.every(question => {
    return answers.value[question.id] !== undefined && answers.value[question.id] !== null
  })
})

const hasPreviousSection = computed(() => {
  const currentIndex = displaySections.value.findIndex(s => s.id === currentSectionId.value)
  return currentIndex > 0
})

const hasNextSection = computed(() => {
  const currentIndex = displaySections.value.findIndex(s => s.id === currentSectionId.value)
  return currentIndex < displaySections.value.length - 1
})

const currentSection = computed(() => {
  const currentIndex = displaySections.value.findIndex(s => s.id === currentSectionId.value)
  return currentIndex + 1
})

const totalSections = computed(() => {
  return displaySections.value.length
})

const showProgress = computed(() => {
  return true // Always show progress
})

// Event handlers
const handleSectionChange = (sectionId) => {
  currentSectionId.value = sectionId
}

const handlePreviousSection = () => {
  if (hasPreviousSection.value) {
    const currentIndex = displaySections.value.findIndex(s => s.id === currentSectionId.value)
    const prevSectionData = displaySections.value[currentIndex - 1]
    handleSectionChange(prevSectionData.id)
  }
}

const handleNextSection = async () => {
  // Save current section answers before proceeding
  await saveSectionAnswers()

  if (hasNextSection.value) {
    const currentIndex = displaySections.value.findIndex(s => s.id === currentSectionId.value)
    const nextSectionData = displaySections.value[currentIndex + 1]
    handleSectionChange(nextSectionData.id)
  } else {
    // This is the last section, submit the final survey
    await submitFinalSurveyResponse()
  }
}

// Handle answer changes
const handleAnswerChange = (questionId, answer) => {
  answers.value[questionId] = answer
}

// Save answers for current section
const saveSectionAnswers = async () => {
  try {
    // Get answers for current section only
    const currentSectionAnswers = {}
    currentQuestions.value.forEach(question => {
      if (answers.value[question.id] !== undefined) {
        currentSectionAnswers[question.id] = answers.value[question.id]
      }
    })

    if (Object.keys(currentSectionAnswers).length === 0) {
      return // No answers to save
    }

    // Transform answers to match expected format
    const formattedAnswers = Object.entries(currentSectionAnswers).map(([questionId, answer]) => {
      const question = displayQuestions.value.find(q => q.id == questionId)
      const formatted = formatAnswer(question, questionId, answer)
      console.log('Formatting answer:', { questionId, answer, questionType: question?.type, formatted })
      return formatted
    })

    // Create form for SSR submission
    const sectionForm = useForm({
      answers: formattedAnswers,
      section_id: currentSectionId.value,
      is_partial: true,
      meta: {
        user_agent: navigator.userAgent,
        section_completed_at: new Date().toISOString(),
        current_section: currentSection.value,
        total_sections: totalSections.value
      }
    })

    console.log('Sending section data via SSR')
    sectionForm.post(`/survey/${props.surveyCode}/save-section`, {
      onSuccess: (page) => {
        if (page.props.responseId) {
          responseId.value = page.props.responseId
        }
        console.log('Section answers saved successfully')
      },
      onError: (errors) => {
        console.error('Error saving section answers:', errors)
      },
      preserveState: true,
      preserveScroll: true
    })
  } catch (err) {
    console.error('Error saving section answers:', err)
    // Don't block navigation on save error, just log it
  }
}

// Submit final survey response
const submitFinalSurveyResponse = async () => {
  try {
    loading.value = true

    // Transform all answers to match expected format
    const formattedAnswers = Object.entries(answers.value).map(([questionId, answer]) => {
      const question = displayQuestions.value.find(q => q.id == questionId)
      return formatAnswer(question, questionId, answer)
    })

    // Create form for SSR submission
    const finalForm = useForm({
      answers: formattedAnswers,
      is_partial: false,
      meta: {
        user_agent: navigator.userAgent,
        completed_at: new Date().toISOString(),
        total_questions_answered: Object.keys(answers.value).length,
        total_questions: totalQuestions.value
      }
    })

    console.log('Submitting final survey via SSR')
    finalForm.post(`/survey/${props.surveyCode}/submit`, {
      onSuccess: () => {
        console.log('Survey completed successfully')
        // Clear session data
        sessionStorage.removeItem('survey_token')
        // Redirect to thank you page will be handled by controller
      },
      onError: (errors) => {
        console.error('Error submitting final survey:', errors)
        error.value = errors.message || 'Gagal mengirim jawaban survey'
        loading.value = false
      }
    })
  } catch (err) {
    console.error('Error submitting final survey:', err)
    error.value = 'Gagal mengirim jawaban survey'
    loading.value = false
  }
}

// Helper function to format answers
const formatAnswer = (question, questionId, answer) => {
  let formattedAnswer = answer

  if (question?.type === 'single_choice' && typeof answer === 'string') {
    // For single choice, send the choice ID
    formattedAnswer = parseInt(answer)
  } else if (question?.type === 'multiple_choice' && Array.isArray(answer)) {
    // For multiple choice, send array of choice IDs
    formattedAnswer = answer.map(id => parseInt(id))
  } else if (question?.type === 'number' && typeof answer === 'string') {
    // For number type, convert to number
    formattedAnswer = parseFloat(answer)
  }

  return {
    question_id: parseInt(questionId),
    choice_id: question?.type === 'single_choice' ? formattedAnswer : null,
    value_text: ['text', 'textarea', 'short_text', 'long_text'].includes(question?.type) ? answer : null,
    value_number: question?.type === 'number' ? formattedAnswer : null,
    value_json: question?.type === 'multiple_choice' ? formattedAnswer : null
  }
}

const generateRespondentToken = () => {
  return 'resp_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now()
}
</script>