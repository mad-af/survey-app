<template>
  <Head title="Hasil Survey" />

  <ProsesSurveyLayout>
    <!-- Header Section -->
    <div class="mb-6 shadow-sm card bg-base-100">
      <div class="card-body">
        <div class="flex gap-3 items-center">
          <div class="p-3 rounded-full bg-primary/10">
            <BarChart3 class="w-6 h-6 text-primary" />
          </div>
          <div>
            <h1 class="text-2xl font-bold">{{ surveyResult.survey.title }}</h1>
            <p class="text-base-content/70">{{ surveyResult.survey.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Overall Score Card -->
    <div class="mb-6 bg-gradient-to-r shadow-sm card from-primary to-secondary text-primary-content">
      <div class="card-body">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="mb-2 text-xl font-semibold">Skor Total Anda</h2>
            <div class="flex gap-2 items-baseline">
              <span class="text-4xl font-bold">{{ surveyResult.score.total_score }}</span>
              <span class="text-lg opacity-80">/ {{ surveyResult.score.max_possible_score }}</span>
            </div>
            <div class="flex gap-2 items-center mt-2">
              <div class="badge badge-accent badge-lg">
                {{ surveyResult.score.percentage }}%
              </div>
              <span v-if="surveyResult.score.category" class="text-sm opacity-90">
                {{ surveyResult.score.category.name }}
              </span>
            </div>
          </div>
          <div class="text-right">
            <Trophy class="w-16 h-16 opacity-80" />
          </div>
        </div>
      </div>
    </div>

    <!-- Category Description -->
    <div v-if="surveyResult.score.category" class="mb-6 alert" :class="getCategoryAlertClass(surveyResult.score.category.color)">
      <Info class="w-5 h-5" />
      <div>
        <h3 class="font-semibold">{{ surveyResult.score.category.name }}</h3>
        <p class="text-sm">{{ surveyResult.score.category.description }}</p>
      </div>
    </div>

    <!-- Section Scores -->
    <div class="shadow-sm card bg-base-100">
      <div class="card-body">
        <div class="flex gap-2 items-center mb-4">
          <PieChart class="w-5 h-5 text-primary" />
          <h2 class="text-xl font-semibold">Skor Per Bagian</h2>
        </div>
        
        <div v-if="surveyResult.sections && surveyResult.sections.length > 0" class="space-y-4">
          <div 
            v-for="(section, index) in surveyResult.sections" 
            :key="section.id"
            class="p-4 rounded-lg border transition-shadow border-base-300 hover:shadow-md"
          >
            <div class="flex justify-between items-center mb-3">
              <div class="flex gap-3 items-center">
                <div class="p-2 rounded-full bg-base-200">
                  <FileText class="w-4 h-4" />
                </div>
                <div>
                  <h3 class="font-medium">{{ section.title }}</h3>
                  <p class="text-sm text-base-content/70">{{ section.description }}</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-lg font-semibold">
                  {{ section.score }} / {{ section.max_score }}
                </div>
                <div class="text-sm text-base-content/70">
                  {{ section.percentage }}%
                </div>
              </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full h-2 rounded-full bg-base-200">
              <div 
                class="h-2 rounded-full transition-all duration-500"
                :class="getProgressBarClass(section.percentage)"
                :style="{ width: section.percentage + '%' }"
              ></div>
            </div>
          </div>
        </div>
        
        <!-- Empty state for sections -->
        <div v-else class="text-center py-8">
          <div class="text-base-content/60">
            <FileText class="w-12 h-12 mx-auto mb-3 opacity-50" />
            <p>Tidak ada data skor per bagian yang tersedia.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 shadow-sm">
      <button class="w-full btn-outline btn" @click="goBack">
        <ArrowLeft class="w-4 h-4" />
        Kembali ke Halaman Entry
      </button>
    </div>
  </ProsesSurveyLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3'
import axios from 'axios'
import ProsesSurveyLayout from '@/Layouts/ProsesSurveyLayout.vue'
import { 
  BarChart3, 
  Trophy, 
  Info, 
  PieChart, 
  FileText, 
  Download, 
  Share2, 
  ArrowLeft 
} from 'lucide-vue-next'

// Define props to receive data from backend
const props = defineProps({
  surveyCode: {
    type: String,
    required: true
  },
  surveyResult: {
    type: Object,
    required: true
  }
})

// Use the surveyResult from props
const { surveyResult } = props

const getCategoryAlertClass = (color) => {
  if (!color) return 'alert-info'
  const colorMap = {
    'success': 'alert-success',
    'warning': 'alert-warning',
    'error': 'alert-error',
    'info': 'alert-info'
  }
  return colorMap[color] || 'alert-info'
}

const getProgressBarClass = (percentage) => {
  if (percentage >= 80) return 'bg-success'
  if (percentage >= 60) return 'bg-warning'
  return 'bg-error'
}

const goBack = async () => {
  try {
    // Clear survey session data from localStorage/sessionStorage
    sessionStorage.removeItem('survey_token')
    localStorage.removeItem('survey_token')
    
    // Call logout endpoint to clear server-side session
    await axios.post('/survey/logout')
    
    // Redirect to entry page using Inertia router
    router.visit('/entry', {
      method: 'get'
    })
  } catch (error) {
    console.error('Error during survey logout:', error)
    // Even if logout fails, still redirect to entry
    router.visit('/entry', {
      method: 'get'
    })
  }
}
</script>