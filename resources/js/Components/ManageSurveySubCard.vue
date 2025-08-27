<template>
  <li class="list-row">
    <!-- Question Number -->
    <div>
      <div class="flex justify-center items-center rounded size-8 bg-primary/10">
        <span class="text-xs font-semibold tracking-normal text-primary/60">{{ String(index + 1).padStart(2, '0') }}</span>
      </div>
    </div>
    
    <!-- Question Info -->
    <div>
      <div class="font-medium">{{ subItem.title }}</div>
      <div class="flex gap-2 items-center mt-1">
        <div class="badge badge-outline badge-xs">
          {{ getQuestionTypeLabel }}
        </div>
        <span v-if="subItem.required" class="badge badge-outline badge-error badge-xs">Required</span>
      </div>
    </div>
    
    <!-- Question Description or Choices -->
    <div class="text-xs list-col-wrap">
      <!-- Show choices for select and radio types -->
      <div v-if="['select', 'radio'].includes(subItem.type) && subItem.choices && subItem.choices.length > 0">
        <!-- <p class="mb-2 text-base-content/70">{{ subItem.description || 'No description available for this question.' }}</p> -->
        <div class="space-y-1">
          <div class="mb-1 text-xs font-medium text-base-content/80">Choices:</div>
          <ul class="space-y-1">
            <li v-for="(choice, choiceIndex) in subItem.choices" :key="choice.id || choiceIndex" class="flex gap-2 items-center">
              <span class="w-4 h-4 rounded-full bg-base-300 flex items-center justify-center text-[10px] font-medium">{{ choiceIndex + 1 }}</span>
              <span class="text-base-content/70">{{ choice.text || choice.label || `Choice ${choiceIndex + 1}` }}</span>
            </li>
          </ul>
        </div>
      </div>
      <!-- Show description for other types -->
      <p v-else class="text-base-content/70">
        <!-- {{ subItem.description || 'No description available for this question.' }} -->
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
      title: '',
      description: '',
      type: 'text',
      required: false,
      order: 1,
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
    'text': Type,
    'email': Mail,
    'number': Hash,
    'date': Calendar,
    'boolean': ToggleLeft,
    'select': List,
    'radio': Circle,
    'textarea': FileText
  }
  return iconMap[props.subItem.type] || Type
})

const getQuestionTypeLabel = computed(() => {
  const typeLabels = {
    'text': 'Text Input',
    'email': 'Email',
    'number': 'Number',
    'date': 'Date',
    'boolean': 'Yes/No',
    'select': 'One Choice',
    'radio': 'Multiple Choice',
    'textarea': 'Long Text'
  }
  return typeLabels[props.subItem.type] || 'Text Input'
})
</script>