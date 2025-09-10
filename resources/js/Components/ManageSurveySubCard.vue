<template>
  <li class="list-row">
    <!-- Question Number -->
    <div>
      <div class="flex justify-center items-center rounded size-8 bg-primary-content">
        <span class="text-xs font-semibold tracking-normal text-primary">{{ String(index + 1).padStart(2, '0') }}</span>
      </div>
    </div>
    
    <!-- Question Info -->
    <div>
      <div class="font-medium">{{ subItem.text }}</div>
      <div class="flex gap-2 items-center mt-1">
        <div class="badge badge-outline badge-base badge-xs">
          {{ getQuestionTypeLabel }}
        </div>
        <span v-if="subItem.required" class="badge badge-outline badge-error badge-xs">Required</span>
        <span v-if="subItem.score_weight > 0" class="badge badge-outline badge-xs">
          Weight: {{ subItem.score_weight }}
        </span>
        <span v-if="getMaxScore > 0" class="badge badge-outline badge-xs">
          Max Score: {{ getMaxScore }}
        </span>
      </div>
    </div>
    
    <!-- Question Description or Choices -->
    <div class="text-xs list-col-wrap">
      <!-- Show choices for select, radio, and single_choice types -->
      <div v-if="['single_choice', 'multiple_choice'].includes(subItem.type) && subItem.choices && subItem.choices.length > 0">
        <div class="space-y-1">
          <div class="mb-2 text-xs font-medium text-base-content/80">Choices:</div>
          <div class="overflow-x-auto border border-base-300">
            <table class="table table-xs">
              <thead>
                <tr class="bg-base-200">
                  <th class="text-xs text-center">Order</th>
                  <th class="text-xs text-center">Label</th>
                  <th class="text-xs text-center">Value</th>
                  <th class="text-xs text-center">Score</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(choice, choiceIndex) in subItem.choices" :key="choice.id || choiceIndex">
                  <td class="text-center">{{ choice.order || choiceIndex + 1 }}</td>
                  <td class="text-center">{{ choice.label || choice.text || `Choice ${choiceIndex + 1}` }}</td>
                  <td class="text-center">{{ choice.value || '-' }}</td>
                  <td class="text-center">{{ choice.score || '0' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Show description for other types -->
      <p v-else class="text-base-content/70">
        <!-- No additional description for text-based questions -->
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-2">
      <div class="tooltip" data-tip="Edit Question">
        <button class="btn btn-xs" @click="$emit('edit-item', subItem)">
          <PencilLine class="size-3" />
        </button>
      </div>

      <div class="tooltip" data-tip="Delete Question">
        <button class="btn btn-xs text-error" @click="$emit('delete-item', subItem)">
          <Trash2 class="size-[1em]" />
        </button>
      </div>
    </div>

  </li>
</template>

<script setup>
import { computed } from 'vue'
import {
  Trash2,
  Type,
  Mail,
  Hash,
  Calendar,
  ToggleLeft,
  List,
  Circle,
  FileText,
  PencilLine
} from 'lucide-vue-next'

// Props
const props = defineProps({
  subItem: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      text: '',
      type: 'short_text',
      required: false,
      order: 1,
      score_weight: '0.00',
      choices: []
    })
  },
  index: {
    type: Number,
    required: true,
    default: 0
  }
})

// Emits
const emit = defineEmits([
  'edit-item',
  'delete-item'
])

// Computed properties
const getQuestionIcon = computed(() => {
  const iconMap = {
    'short_text': Type,
    'long_text': FileText,
    'single_choice': Circle,
    'multiple_choice': List,
    'number': Hash,
    'email': Mail,
    'date': Calendar,
    'boolean': ToggleLeft,
    'text': Type,
    'select': List,
    'radio': Circle,
    'textarea': FileText
  }
  return iconMap[props.subItem.type] || Type
})

const getQuestionTypeLabel = computed(() => {
  const typeLabels = {
    'short_text': 'Short Text',
    'long_text': 'Long Text',
    'single_choice': 'Single Choice',
    'multiple_choice': 'Multiple Choice',
    'number': 'Number',
    'email': 'Email',
    'date': 'Date',
    'boolean': 'Yes/No',
    'text': 'Text Input',
    'select': 'One Choice',
    'radio': 'Multiple Choice',
    'textarea': 'Long Text'
  }
  return typeLabels[props.subItem.type] || 'Short Text'
})

const getMaxScore = computed(() => {
  if (!props.subItem.choices || props.subItem.choices.length === 0) {
    return 0
  }
  
  const scores = props.subItem.choices.map(choice => parseFloat(choice.score) || 0)
  return Math.max(...scores)
})
</script>