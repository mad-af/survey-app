<template>
  <div class="w-full">
    <!-- Main Section Card -->
    <div class="w-full shadow-sm card bg-base-100 card-sm">
      <div class="card-body">
        <div class="flex justify-between">
          <div>
            <h3 class="text-xs font-semibold tracking-wider uppercase text-primary">
              <span>#</span>
              Section {{ section.order || 1 }}
            </h3>
            <h2 class="text-lg card-title">{{ section.title || 'Untitled Section' }}</h2>
          </div>
          <div class="flex gap-2 items-start">
            <div class="tooltip" data-tip="Edit Section">
              <button class="font-medium btn btn-xs" @click="$emit('edit-section', section)">
                <PencilLine class="w-3 h-3" />
              </button>
            </div>
            <div class="tooltip" data-tip="Delete Section">
              <button class="font-medium btn btn-xs text-error" @click="$emit('delete-section', section)">
                <Trash2 class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>
        <p class="text-sm text-base-content/70">
          {{ section.description || 'No description available for this section.' }}
        </p>

        <!-- Questions Count -->
        <div v-if="section.questions && section.questions.length > 0" class="mt-2">
          <span class="text-xs text-base-content/50">
            {{ section.questions.length }} question{{ section.questions.length > 1 ? 's' : '' }}
          </span>
        </div>

        <!-- Questions List -->
        <ul class="outline outline-base-300 list bg-base-100 rounded-box">
          <li class="flex justify-between p-4 pb-2 text-xs tracking-wide">
            List questions
            <div class="tooltip" data-tip="Add Question">
              <button class="font-medium btn btn-xs" @click="$emit('add-question', section)">
                <CopyPlus class="w-3 h-3" />
              </button>
            </div>
          </li>

          <ManageSurveySubCard v-if="section.questions && section.questions.length > 0"
            v-for="(question, index) in section.questions" :key="question.id" :sub-item="question" :index="index"
            @edit-item="handleEditQuestion" @delete-item="handleDeleteQuestion" />

          <!-- Questions Empty -->
          <li v-else class="flex flex-col items-center pb-4 text-xs tracking-wide text-base-content/30">
            <span>
              No questions yet
            </span>
          </li>
        </ul>

      </div>
    </div>

  </div>
</template>

<script setup>
import { Trash2, CopyPlus, PencilLine } from 'lucide-vue-next'
import ManageSurveySubCard from './ManageSurveySubCard.vue'

// Props
const props = defineProps({
  section: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      title: '',
      description: '',
      order: 1,
      questions: []
    })
  }
})

// Emits
const emit = defineEmits([
  'edit-section',
  'delete-section',
  'add-question',
  'edit-question',
  'delete-question'
])

// Event handlers for sub-items
const handleEditQuestion = (question) => {
  emit('edit-question', question, props.section)
}

const handleDeleteQuestion = (question) => {
  emit('delete-question', question, props.section)
}
</script>