<template>
  <div class="shadow-xs card bg-base-100">
    <div class="card-body">
      <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center lg:space-y-0 border-b-primary">
        <!-- Logo and Survey Title -->
        <div class="flex items-center space-x-2 lg:space-x-4">
          <div class="flex items-center space-x-3">
            <img src="/beraksi.webp" alt="AKUSUKA Logo" class="h-6 lg:h-8 lg:w-32" />
            <div class="hidden sm:block">
              <h1 class="text-lg font-bold lg:text-xl text-base-content">AKUSUKA</h1>
              <p class="text-xs text-base-content/70">Partisipasi Mudah Manfaat Nyata</p>
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div v-if="showProgress"
          class="flex flex-col items-start space-y-2 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
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

      <div class="divider"></div>

      <!-- Survey Sections Steps -->
      <div v-if="sections.length > 0">
        <div class="overflow-x-auto pb-4" ref="scrollContainer">
          <div class="flex space-x-4 min-w-max">
            <div v-for="(section, index) in sections" :key="section.id"
              :ref="el => { if (currentSectionId === section.id) activeCardRef = el }"
              class="flex-shrink-0 transition-all cursor-pointer" @click="handleSectionChange(section.id)">
              <div class="border-2 shadow-md transition-all duration-400 card bg-base-100 hover:shadow-lg" :class="{
                'border-primary bg-primary/5': currentSectionId === section.id,
                'border-base-300 hover:border-primary/50': currentSectionId !== section.id
              }">
                <div class="p-4 transition-all duration-300 min-h-36 card-body" :class="{
                  'min-w-[200px] max-w-[250px] lg:min-w-[280px] lg:max-w-[560px]': currentSectionId === section.id,
                  'min-w-[200px] max-w-[250px]': currentSectionId !== section.id
                }">
                  <!-- Step Number -->
                  <div class="flex items-center mb-3 space-x-3">
                    <div class="flex-shrink-0">
                      <div
                        class="flex justify-center items-center w-8 h-8 text-sm font-bold rounded-full transition-all duration-300"
                        :class="{
                          'bg-primary text-primary-content w-8 h-8': currentSectionId === section.id,
                          'bg-base-300 text-base-content': currentSectionId !== section.id
                        }">
                        {{ index + 1 }}
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h3 class="font-semibold transition-all duration-300" :class="{
                        'text-primary text-base': currentSectionId === section.id,
                        'text-base-content text-sm truncate': currentSectionId !== section.id
                      }">
                        {{ section.title || `Section ${index + 1}` }}
                      </h3>
                    </div>
                  </div>

                  <!-- Description -->
                  <p v-if="section.description"
                    class="text-xs leading-relaxed transition-all duration-300 text-base-content/70" :class="{
                      'line-clamp-1': currentSectionId !== section.id
                    }">
                    {{ section.description }}
                  </p>

                  <!-- Status Indicator -->
                  <div class="flex justify-between items-center mt-3">
                    <div class="text-xs font-medium transition-all duration-300" :class="{
                      'text-primary text-sm': currentSectionId === section.id,
                      'text-base-content/50': currentSectionId !== section.id
                    }">
                      {{ currentSectionId === section.id ? 'Current Section' : 'Section ' + (index + 1) }}
                    </div>
                    <div v-if="currentSectionId === section.id" class="w-3 h-3 rounded-full animate-pulse bg-primary">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


      </div>



    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick, watch } from 'vue'
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
const emit = defineEmits([
  'section-change'
])

// Refs for scroll functionality
const scrollContainer = ref(null)
const activeCardRef = ref(null)

// Methods
const handleSectionChange = (sectionId) => {
  emit('section-change', sectionId)
}

// Watch for currentSectionId changes and scroll to active card
watch(() => props.currentSectionId, async () => {
  await nextTick()
  
  if (activeCardRef.value && scrollContainer.value) {
    const container = scrollContainer.value
    const activeCard = activeCardRef.value

    // Calculate the position to center the active card
    const containerWidth = container.clientWidth
    const cardLeft = activeCard.offsetLeft
    const cardWidth = activeCard.offsetWidth
    
    const isMobile = window.innerWidth < 1024
    const scrollLeft = isMobile ? 
      cardLeft - (containerWidth / 2) + (cardWidth / 2):
      cardLeft - (containerWidth) + (cardWidth)
    // Smooth scroll to the calculated position
    container.scrollTo({
      left: scrollLeft,
      behavior: 'smooth'
    })
  }
}, { immediate: true })

// Computed properties
const progressPercentage = computed(() => {
  if (props.totalSections === 0) return 0
  return Math.round((props.currentSection / props.totalSections) * 100)
})
</script>