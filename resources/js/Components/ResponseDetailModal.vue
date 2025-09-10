<template>
  <div v-if="response" class="modal modal-open">
    <div class="max-w-4xl modal-box">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold">Response Details - #{{ response.id }}</h3>
        <button class="btn btn-sm btn-circle btn-ghost" @click="$emit('close')">
          <X class="w-4 h-4" />
        </button>
      </div>
      
      <!-- Response Overview -->
      <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <div class="card bg-base-200">
          <div class="card-body">
            <h4 class="text-base card-title">Status & Progress</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span>Status:</span>
                <span class="badge" :class="getStatusBadgeClass(response.status)">
                  {{ getStatusLabel(response.status) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span>Progress:</span>
                <span class="badge" :class="getStepBadgeClass(response.current_step)">
                  {{ getStepLabel(response.current_step) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span>Started:</span>
                <span>{{ formatDate(response.started_at) }}</span>
              </div>
              <div v-if="response.submitted_at" class="flex justify-between">
                <span>Submitted:</span>
                <span>{{ formatDate(response.submitted_at) }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="card bg-base-200">
          <div class="card-body">
            <h4 class="text-base card-title">Personal Information</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span>Name:</span>
                <span>{{ response.respondent?.name || 'Anonymous' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Email:</span>
                <span>{{ response.respondent?.email || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Phone:</span>
                <span>{{ response.respondent?.phone || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Gender:</span>
                <span>{{ getGenderLabel(response.respondent?.gender) || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Birth Year:</span>
                <span>{{ response.respondent?.birth_year || '-' }}</span>
              </div>
              <div v-if="response.respondent?.external_id" class="flex justify-between">
                <span>External ID:</span>
                <span>{{ response.respondent.external_id }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card bg-base-200">
          <div class="card-body">
            <h4 class="text-base card-title">Professional Information</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span>Organization:</span>
                <span>{{ response.respondent?.organization || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Department:</span>
                <span>{{ response.respondent?.department || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Role:</span>
                <span>{{ response.respondent?.role_title || '-' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Location Information -->
      <div v-if="response.respondent?.location" class="mb-6">
        <div class="card bg-base-200">
          <div class="card-body">
            <h4 class="text-base card-title">Location Information</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span>Province:</span>
                <span>{{ response.respondent.location.province_name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Regency:</span>
                <span>{{ response.respondent.location.regency_name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>District:</span>
                <span>{{ response.respondent.location.district_name || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Village:</span>
                <span>{{ response.respondent.location.village_name || '-' }}</span>
              </div>
              <div v-if="response.respondent.location.detailed_address" class="flex justify-between">
                <span>Detailed Address:</span>
                <span>{{ response.respondent.location.detailed_address }}</span>
              </div>
              <div v-if="response.respondent.location.latitude && response.respondent.location.longitude" class="flex justify-between">
                <span>Coordinates:</span>
                <span>{{ response.respondent.location.latitude }}, {{ response.respondent.location.longitude }}</span>
              </div>
              <div class="flex justify-between">
                <span>Full Address:</span>
                <span>{{ getFullAddress(response.respondent.location) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Score Information -->
      <div v-if="response.score" class="mb-6">
        <div class="card bg-base-200">
          <div class="card-body">
            <h4 class="text-base card-title">Overall Score Summary</h4>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span>Total Score:</span>
                  <span class="font-semibold">{{ response.score.total_score }}/{{ response.score.max_possible_score }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Percentage:</span>
                  <span class="font-semibold">{{ response.score.percentage }}%</span>
                </div>
              </div>
              <div v-if="response.score.result_category" class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span>Category:</span>
                  <span class="badge" :class="getCategoryBadgeClass(response.score.result_category.color)">
                    {{ response.score.result_category.name }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span>Score Range:</span>
                  <span>{{ response.score.result_category.min_score }} - {{ response.score.result_category.max_score }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section Scores -->
        <div v-if="response.score.section_scores && response.score.section_scores.length > 0" class="mb-6">
          <div class="card bg-base-200">
            <div class="card-body">
              <h4 class="text-base card-title">Section-wise Results</h4>
              <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div v-for="sectionScore in response.score.section_scores" :key="sectionScore.section_id" class="p-3 rounded-lg bg-base-100">
                  <div class="flex justify-between items-center mb-2">
                    <h5 class="text-sm font-medium">{{ getSectionTitle(sectionScore.section_id) }}</h5>
                    <span class="text-xs badge badge-outline">Section {{ sectionScore.section_id }}</span>
                  </div>
                  <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                      <span>Score:</span>
                      <span class="font-semibold">{{ sectionScore.score }}/{{ sectionScore.max_score }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span>Percentage:</span>
                      <span class="font-semibold">{{ Math.round((sectionScore.score / sectionScore.max_score) * 100) }}%</span>
                    </div>
                    <div class="mt-2 w-full h-1.5 rounded-full bg-base-300">
                      <div class="h-1.5 rounded-full bg-primary" :style="{ width: Math.round((sectionScore.score / sectionScore.max_score) * 100) + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Score Data -->
      <div v-else class="mb-6">
        <div class="card bg-base-200">
          <div class="text-center card-body">
            <div class="text-base-content/60">
              <BarChart3 class="mx-auto mb-2 w-8 h-8 opacity-50" />
              <p class="font-medium">No score data available</p>
              <p class="text-sm">Score will be calculated when the response is completed.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Questions and Answers -->
      <div v-if="response.answers && response.answers.length > 0" class="mb-6">
        <div class="card bg-base-200">
          <div class="card-body">
            <h4 class="mb-4 text-base card-title">Questions & Answers ({{ response.answers.length }})</h4>
            <div class="space-y-4">
              <div v-for="answer in response.answers" :key="answer.id" class="p-4 rounded-lg bg-base-100">
                <!-- Question Information -->
                <div class="mb-3">
                  <div class="flex justify-between items-start mb-2">
                    <h5 class="text-sm font-medium text-base-content">{{ getQuestionText(answer.question_id) }}</h5>
                    <div class="flex gap-2">
                      <span class="badge badge-xs" :class="getQuestionTypeBadgeClass(getQuestionType(answer.question_id))">
                        {{ getQuestionTypeLabel(getQuestionType(answer.question_id)) }}
                      </span>
                      <span v-if="isQuestionRequired(answer.question_id)" class="badge badge-xs badge-error">Required</span>
                    </div>
                  </div>
                  <p class="text-xs text-base-content/60">Section: {{ getSectionTitle(getQuestionSectionId(answer.question_id)) }}</p>
                </div>

                <!-- Answer Information -->
                <div class="pt-3 border-t border-base-300">
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <p class="mb-1 text-xs font-medium text-base-content/70">Answer:</p>
                      <div class="text-sm">
                        <!-- Choice-based answer -->
                        <div v-if="answer.choice_id" class="flex gap-2 items-center">
                          <span class="badge badge-sm badge-primary">{{ getChoiceText(answer.choice_id) }}</span>
                          <span v-if="getChoiceScore(answer.choice_id)" class="text-xs text-base-content/60">
                            ({{ getChoiceScore(answer.choice_id) }} points)
                          </span>
                        </div>
                        <!-- Text answer -->
                        <div v-else-if="answer.value_text" class="p-2 rounded bg-base-200">
                          <p class="text-sm">{{ answer.value_text }}</p>
                        </div>
                        <!-- Number answer -->
                        <div v-else-if="answer.value_number !== null" class="p-2 rounded bg-base-200">
                          <p class="font-mono text-sm">{{ answer.value_number }}</p>
                        </div>
                        <!-- JSON answer -->
                        <div v-else-if="answer.value_json" class="p-2 rounded bg-base-200">
                          <p class="text-sm">{{ formatJsonAnswer(answer.value_json) }}</p>
                        </div>
                        <!-- No answer -->
                        <div v-else class="text-base-content/50">
                          <p class="text-sm italic">No answer provided</p>
                        </div>
                      </div>
                    </div>
                    <div class="text-xs text-base-content/60">
                      <p>Question #{{ getQuestionOrder(answer.question_id) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Answers Data -->
      <div v-else class="mb-6">
        <div class="card bg-base-200">
          <div class="text-center card-body">
            <div class="text-base-content/60">
              <FileText class="mx-auto mb-2 w-8 h-8 opacity-50" />
              <p class="font-medium">No answers available</p>
              <p class="text-sm">Response baru dimulai tanpa answers</p>
              <p class="text-sm font-medium">Tidak ada data answers tersedia</p>
              <p class="text-sm font-medium">Response tidak memiliki answers yang tercatat</p>
              <p class="text-sm font-medium">Gagal memuat answers</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Actions -->
      <div class="flex gap-2 justify-end pt-4 mt-6 border-t border-base-300">
        <button class="btn btn-sm btn-outline" @click="$emit('close')">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { X, BarChart3, FileText } from 'lucide-vue-next'

// Props
const props = defineProps({
  response: {
    type: Object,
    default: null
  },
  survey: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['close'])

// Helper functions
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'completed':
      return 'badge-success'
    case 'in_progress':
      return 'badge-warning'
    case 'started':
      return 'badge-info'
    case 'abandoned':
      return 'badge-error'
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
    case 'started':
      return 'Started'
    case 'abandoned':
      return 'Abandoned'
    default:
      return 'Unknown'
  }
}

const getStepBadgeClass = (step) => {
  switch (step) {
    case 1:
      return 'badge-info'
    case 2:
      return 'badge-warning'
    case 3:
      return 'badge-success'
    default:
      return 'badge-ghost'
  }
}

const getStepLabel = (step) => {
  switch (step) {
    case 1:
      return 'Respondent Data'
    case 2:
      return 'Questions'
    case 3:
      return 'Result'
    default:
      return 'Unknown'
  }
}

const getGenderLabel = (gender) => {
  switch (gender) {
    case 'male':
      return 'Male'
    case 'female':
      return 'Female'
    case 'other':
      return 'Other'
    case 'prefer_not_to_say':
      return 'Prefer not to say'
    default:
      return 'Not specified'
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
  try {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    return '-'
  }
}

const getFullAddress = (location) => {
  if (!location) return '-'
  
  const parts = []
  if (location.village_name) parts.push(location.village_name)
  if (location.district_name) parts.push(location.district_name)
  if (location.regency_name) parts.push(location.regency_name)
  if (location.province_name) parts.push(location.province_name)
  
  let address = parts.join(', ')
  
  if (location.detailed_address) {
    address = location.detailed_address + ', ' + address
  }
  
  return address || '-'
}

const getSectionTitle = (sectionId) => {
  if (!props.survey?.sections) return `Section ${sectionId}`
  const section = props.survey.sections.find(s => s.id === sectionId)
  return section ? section.title : `Section ${sectionId}`
}

const getQuestionTypeBadgeClass = (type) => {
  const typeClasses = {
    'text': 'badge-primary',
    'textarea': 'badge-secondary', 
    'radio': 'badge-accent',
    'checkbox': 'badge-info',
    'select': 'badge-success',
    'number': 'badge-warning',
    'date': 'badge-error'
  }
  return typeClasses[type] || 'badge-ghost'
}

const getQuestionTypeLabel = (type) => {
  const typeLabels = {
    'text': 'Text Input',
    'textarea': 'Long Text',
    'radio': 'Single Choice',
    'checkbox': 'Multiple Choice', 
    'select': 'Dropdown',
    'number': 'Number',
    'date': 'Date'
  }
  return typeLabels[type] || type
}

const getQuestionText = (questionId) => {
  if (!props.survey?.sections) return `Question ${questionId}`
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.text
    }
  }
  return `Question ${questionId}`
}

const getQuestionType = (questionId) => {
  if (!props.survey?.sections) return 'text'
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.type
    }
  }
  return 'text'
}

const isQuestionRequired = (questionId) => {
  if (!props.survey?.sections) return false
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.required
    }
  }
  return false
}

const getQuestionSectionId = (questionId) => {
  if (!props.survey?.sections) return null
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return section.id
    }
  }
  return null
}

const getQuestionOrder = (questionId) => {
  if (!props.survey?.sections) return 0
  for (const section of props.survey.sections) {
    if (section.questions) {
      const question = section.questions.find(q => q.id === questionId)
      if (question) return question.order
    }
  }
  return 0
}

const getChoiceText = (choiceId) => {
  if (!choiceId || !props.survey?.sections) return `Choice ${choiceId}`

  // Convert choiceId to number for comparison
  const targetChoiceId = parseInt(choiceId)
  
  for (const section of props.survey.sections) {
    if (section.questions) {
      for (const question of section.questions) {
        if (question.choices && Array.isArray(question.choices)) {
          const choice = question.choices.find(c => {
            const currentChoiceId = parseInt(c.id)
            return currentChoiceId === targetChoiceId
          })
          if (choice) {
            return `${choice.label} [value: ${choice.value}]` 
          }
        }
      }
    }
  }
  return `Choice ${choiceId}`
}

const getChoiceScore = (choiceId) => {
  if (!choiceId || !props.survey?.sections) return null
  
  // Convert choiceId to number for comparison
  const targetChoiceId = parseInt(choiceId)
  
  for (const section of props.survey.sections) {
    if (section.questions) {
      for (const question of section.questions) {
        if (question.choices && Array.isArray(question.choices)) {
          const choice = question.choices.find(c => {
            const currentChoiceId = parseInt(c.id)
            return currentChoiceId === targetChoiceId
          })
          if (choice && choice.score !== undefined) {
            return choice.score
          }
        }
      }
    }
  }
  return null
}

const formatJsonAnswer = (jsonValue) => {
  if (!jsonValue) return 'No answer'
  if (Array.isArray(jsonValue)) {
    return jsonValue.join(', ')
  }
  if (typeof jsonValue === 'object') {
    return JSON.stringify(jsonValue, null, 2)
  }
  return String(jsonValue)
}
</script>