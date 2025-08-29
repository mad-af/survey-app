<template>
  <div class="shadow-xs card bg-base-100">
    <div class="card-body">
      <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center lg:space-y-0">
        <!-- Logo and Survey Title -->
        <div class="flex items-center space-x-2 lg:space-x-4">
          <div class="flex items-center space-x-2">
            <FileText class="w-6 h-6 lg:w-8 lg:h-8 text-primary" />
            <div>
              <h1 class="text-lg font-bold lg:text-xl text-base-content">SurveyApp</h1>
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div v-if="showProgress" class="flex flex-col items-start space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
          <div class="text-xs whitespace-nowrap sm:text-sm text-base-content/70">
            Section {{ currentSection }} of {{ totalSections }}
          </div>
          <div class="w-full sm:w-24 lg:w-32">
            <progress class="w-full progress progress-primary" :value="progressPercentage" max="100"></progress>
          </div>
          <div class="text-xs font-medium whitespace-nowrap sm:text-sm text-base-content">
            {{ progressPercentage }}%
          </div>
        </div>
      </div>

      <!-- Survey Sections Tabs -->
      <div class="pt-4 mt-4 border-t border-base-300">
        <div class="overflow-x-auto flex-col tabs tabs-lift sm:flex-row">
          <template v-for="(section, index) in sections" :key="section.id">
            <input type="radio" :name="'survey_sections'" class="flex-shrink-0 text-xs tab sm:text-sm" :aria-label="section.title"
              :checked="section.id === currentSectionId" @change="$emit('section-change', section.id)" />
            <div class="p-3 sm:p-4 border-base-300 tab-content bg-base-100">
              <div class="space-y-2 sm:space-y-3">
                <h3 class="text-base font-semibold sm:text-lg text-base-content">{{ section.title }}</h3>
                <p class="text-xs leading-relaxed sm:text-sm text-base-content/70">{{ section.description }}</p>
                <div class="space-y-1 sm:space-y-2">
                  <div v-if="section.id === 1" class="flex items-start space-x-2 sm:items-center">
                    <div class="flex-shrink-0 mt-1 w-2 h-2 rounded-full bg-primary sm:mt-0"></div>
                    <span class="text-xs leading-relaxed sm:text-sm text-base-content/80">Isi data pribadi Anda dengan lengkap</span>
                  </div>
                  <div v-else-if="section.id === 2" class="flex items-start space-x-2 sm:items-center">
                    <div class="flex-shrink-0 mt-1 w-2 h-2 rounded-full bg-secondary sm:mt-0"></div>
                    <span class="text-xs leading-relaxed sm:text-sm text-base-content/80">Berikan penilaian objektif terhadap layanan</span>
                  </div>
                  <div v-else-if="section.id === 3" class="flex items-start space-x-2 sm:items-center">
                    <div class="flex-shrink-0 mt-1 w-2 h-2 rounded-full bg-accent sm:mt-0"></div>
                    <span class="text-xs leading-relaxed sm:text-sm text-base-content/80">Sampaikan masukan konstruktif untuk perbaikan</span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { FileText } from 'lucide-vue-next'

// Define props
const props = defineProps({
  surveyTitle: {
    type: String,
    default: 'Survey'
  },
  sectionDescription: {
    type: String,
    default: ''
  },
  currentSection: {
    type: Number,
    default: 1
  },
  totalSections: {
    type: Number,
    default: 1
  },
  showProgress: {
    type: Boolean,
    default: true
  },
  sections: {
    type: Array,
    default: () => []
  },
  currentSectionId: {
    type: [String, Number],
    default: null
  }
})

// Define emits
defineEmits([
  'section-change'
])

// Computed properties
const progressPercentage = computed(() => {
  if (props.totalSections === 0) return 0
  return Math.round((props.currentSection / props.totalSections) * 100)
})
</script>