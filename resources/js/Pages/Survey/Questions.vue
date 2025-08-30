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
          :question-number="index + 1" v-model="formAnswers[question.id]" />
      </div>

      <!-- Navigation Buttons -->
      <SurveyNavigation :answered-questions="answeredQuestions" :total-questions="totalQuestions"
        :has-previous-section="hasPreviousSection" :has-next-section="hasNextSection" :can-proceed="canProceed"
        @previous-section="handlePreviousSection" @next-section="handleNextSection" />
    </template>
  </ProsesSurveyLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import ProsesSurveyLayout from '@/Layouts/ProsesSurveyLayout.vue'
import SurveyNavbar from '@/Components/SurveyNavbar.vue'
import SurveyQuestionCard from '@/Components/SurveyQuestionCard.vue'
import SurveyNavigation from '@/Components/SurveyNavigation.vue'
import { useForm } from '@inertiajs/vue3'

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

// Response tracking
const responseId = ref(null)

// Form answers using reactive refs
const formAnswers = ref({})

// Initialize form answers for all questions
const initializeFormAnswers = () => {
  const answers = {}
  displayQuestions.value.forEach(question => {
    if (question.type === 'single_choice') {
      answers[question.id] = null
    } else if (question.type === 'multiple_choice') {
      answers[question.id] = []
    } else {
      answers[question.id] = ''
    }
  })
  formAnswers.value = answers
}

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
    responseId.value = props.existingResponse.id
  }

  // Initialize form answers
  initializeFormAnswers()

  // Populate with existing data
  populateFormAnswers()
}

// Populate form answers with existing data
const populateFormAnswers = () => {
  if (!props.existingResponse || !props.existingResponse.answers) {
    return
  }

  Object.entries(props.existingResponse.answers).forEach(([questionId, answerData]) => {
    const question = displayQuestions.value.find(q => q.id == questionId)
    if (!question) return

    if (question.type === 'single_choice' && answerData.choice_id) {
      formAnswers.value[questionId] = answerData.choice_id
    } else if (question.type === 'multiple_choice' && answerData.value_json) {
      formAnswers.value[questionId] = JSON.parse(answerData.value_json)
    } else if (question.type === 'short_text' || question.type === 'long_text') {
      formAnswers.value[questionId] = answerData.value_text || ''
    } else if (question.type === 'number') {
      formAnswers.value[questionId] = answerData.value_number || ''
    } else if (question.type === 'date') {
      formAnswers.value[questionId] = answerData.value_text || ''
    }
  })
}

// Watch for section changes to populate form data
watch(currentSectionId, () => {
  populateFormAnswers()
})

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
  // Use navigationKey to force reactivity
  // navigationKey.value
  let count = 0
  displayQuestions.value.forEach(question => {
    if (isQuestionAnswered(question)) {
      count++
    }
  })
  return count
})

const totalQuestions = computed(() => {
  return displayQuestions.value.length
})

const canProceed = computed(() => {
  return currentQuestions.value.every(question => {
    return isQuestionAnswered(question)
  })
})

// Helper function to check if a question is answered
const isQuestionAnswered = (question) => {
  const answer = formAnswers.value[question.id]

  if (question.type === 'single_choice') {
    return answer !== null && answer !== ''
  } else if (question.type === 'multiple_choice') {
    return Array.isArray(answer) && answer.length > 0
  } else {
    return answer !== null && answer !== '' && String(answer).trim() !== ''
  }
}

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

// Save answers for current section
const saveSectionAnswers = async () => {
  try {
    // Get answers from reactive refs
    const currentSectionAnswers = {}

    currentQuestions.value.forEach(question => {
      const answer = formAnswers.value[question.id]
      let processedAnswer = null

      if (question.type === 'single_choice') {
        if (answer !== null && answer !== '') {
          processedAnswer = parseInt(answer)
        }
      } else if (question.type === 'multiple_choice') {
        if (Array.isArray(answer) && answer.length > 0) {
          processedAnswer = answer.map(val => parseInt(val))
        }
      } else {
        if (answer !== null && answer !== '') {
          if (question.type === 'number') {
            processedAnswer = parseFloat(answer)
          } else {
            processedAnswer = answer
          }
        }
      }

      if (processedAnswer !== null && processedAnswer !== '' && !(Array.isArray(processedAnswer) && processedAnswer.length === 0)) {
        currentSectionAnswers[question.id] = processedAnswer
      }
    })

    if (Object.keys(currentSectionAnswers).length === 0) {
      return // No answers to save
    }

    // Transform answers to match expected format
    const formattedAnswers = Object.entries(currentSectionAnswers).map(([questionId, answer]) => {
      const question = displayQuestions.value.find(q => q.id == questionId)
      const formatted = formatAnswer(question, questionId, answer)
      return formatted
    })

    // Use web endpoint for background saving (fire-and-forget)
    const payload = {
      answers: formattedAnswers,
      section_id: currentSectionId.value,
      is_partial: true,
      meta: {
        user_agent: navigator.userAgent,
        section_completed_at: new Date().toISOString(),
        current_section: currentSection.value,
        total_sections: totalSections.value
      }
    }

    // Fire-and-forget web call with timeout
    const controller = new AbortController()

    fetch('/api/survey/question-partials', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(payload),
      signal: controller.signal
    })
    .then(response => {
      if (response.ok) {
        return response.json()
      }
      throw new Error(`HTTP ${response.status}: ${response.statusText}`)
    })
    .then(data => {
      console.log('Section answers saved successfully:', data)
      if (data.responseId) {
        responseId.value = data.responseId
      }
    })
    .catch(error => {
      clearTimeout(timeoutId)
      if (error.name !== 'AbortError') {
        console.error('Error saving section answers:', error)
      }
      // Don't block navigation on save error, just log it
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

    // Get all answers from reactive refs
    const allAnswers = {}

    displayQuestions.value.forEach(question => {
      const answer = formAnswers.value[question.id]
      let processedAnswer = null

      if (question.type === 'single_choice') {
        if (answer !== null && answer !== '') {
          processedAnswer = parseInt(answer)
        }
      } else if (question.type === 'multiple_choice') {
        if (Array.isArray(answer) && answer.length > 0) {
          processedAnswer = answer.map(val => parseInt(val))
        }
      } else {
        if (answer !== null && answer !== '') {
          if (question.type === 'number') {
            processedAnswer = parseFloat(answer)
          } else {
            processedAnswer = answer
          }
        }
      }

      if (processedAnswer !== null && processedAnswer !== '' && !(Array.isArray(processedAnswer) && processedAnswer.length === 0)) {
        allAnswers[question.id] = processedAnswer
      }
    })

    // Transform all answers to match expected format
    const formattedAnswers = Object.entries(allAnswers).map(([questionId, answer]) => {
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
        total_questions_answered: Object.keys(allAnswers).length,
        total_questions: totalQuestions.value
      }
    })

    finalForm.post(`/survey/questions`, {
      onSuccess: () => {
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
  return {
    question_id: parseInt(questionId),
    choice_id: question?.type === 'single_choice' ? answer : null,
    value_text: ['short_text', 'long_text', 'date'].includes(question?.type) ? answer : null,
    value_number: question?.type === 'number' ? answer : null,
    value_json: question?.type === 'multiple_choice' ? JSON.stringify(answer) : null
  }
}
</script>