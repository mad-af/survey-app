<template>
  <div class="shadow-xs card bg-base-100">
    <div class="card-body">
      <!-- Question Header -->
      <div class="flex justify-between items-start mb-4">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <span
              class="inline-flex justify-center items-center w-8 h-8 text-sm font-medium rounded-full bg-primary text-primary-content">
              {{ questionNumber }}
            </span>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-medium text-base-content">
              {{ question.text }}
              <span v-if="question.required" class="ml-1 text-error">*</span>
            </h3>
            <div class="flex items-center mt-1 space-x-2">
              <span class="badge badge-sm badge-outline">{{ getQuestionTypeLabel(question.type) }}</span>
              <span v-if="question.required" class="badge badge-sm badge-error">Required</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Question Input Area -->
      <div>
        <!-- Short Text -->
        <div v-if="question.type === 'short_text'">
          <input type="text" class="w-full input input-bordered" :placeholder="'Masukkan jawaban Anda...'" 
            :required="question.required" :name="`question_${question.id}`" :ref="`question_${question.id}`" />
        </div>

        <!-- Long Text -->
        <div v-else-if="question.type === 'long_text'">
          <textarea class="w-full h-24 resize-none textarea textarea-bordered" 
            :placeholder="'Masukkan jawaban Anda...'" :required="question.required" 
            :name="`question_${question.id}`" :ref="`question_${question.id}`"></textarea>
        </div>

        <!-- Number -->
        <div v-else-if="question.type === 'number'">
          <input type="number" class="w-full input input-bordered" :placeholder="'Masukkan angka...'" 
            :required="question.required" :name="`question_${question.id}`" :ref="`question_${question.id}`" />
        </div>

        <!-- Date -->
        <div v-else-if="question.type === 'date'">
          <input type="date" class="w-full input input-bordered" :required="question.required" 
            :name="`question_${question.id}`" :ref="`question_${question.id}`" />
        </div>

        <!-- Single Choice -->
        <div v-else-if="question.type === 'single_choice' && question.choices" class="space-y-2">
          <div v-for="choice in question.choices" :key="choice.id" class="form-control">
            <label class="justify-start space-x-3 cursor-pointer label">
              <input type="radio" :name="`question_${question.id}`" :value="choice.id" class="radio radio-primary" 
                :required="question.required" :ref="`question_${question.id}_${choice.id}`" />
              <span class="label-text">{{ choice.label }}</span>
            </label>
          </div>
        </div>

        <!-- Multiple Choice -->
        <div v-else-if="question.type === 'multiple_choice' && question.choices" class="space-y-2">
          <div v-for="choice in question.choices" :key="choice.id" class="form-control">
            <label class="justify-start space-x-3 cursor-pointer label">
              <input type="checkbox" :name="`question_${question.id}[]`" :value="choice.id" class="checkbox checkbox-primary" 
                :ref="`question_${question.id}_${choice.id}`" />
              <span class="label-text">{{ choice.label }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Define props
const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  questionNumber: {
    type: Number,
    required: true
  }
})

// Question type labels
const getQuestionTypeLabel = (type) => {
  const typeLabels = {
    'short_text': 'Teks Pendek',
    'long_text': 'Teks Panjang',
    'single_choice': 'Pilihan Tunggal',
    'multiple_choice': 'Pilihan Ganda',
    'number': 'Angka',
    'date': 'Tanggal'
  }
  return typeLabels[type] || type
}
</script>