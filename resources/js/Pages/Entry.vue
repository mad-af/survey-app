<template>

  <Head title="Masuk Survey" />

  <RawCenteredLayout>
    <div class="container px-4 py-8 mx-auto">
      <div class="grid grid-cols-1 gap-8 mx-auto max-w-7xl lg:grid-cols-3">
        <!-- Left Column - Public Surveys -->
        <div class="space-y-6 lg:col-span-2">
          <!-- Public Surveys Section -->
          <ContentCard v-if="publicSurveys && publicSurveys.length > 0" title="📋 Survey Tersedia"
            subtitle="Pilih survey yang ingin Anda ikuti" maxWidth="max-w-none">
            <div class="grid gap-4 md:grid-cols-2">
              <div v-for="survey in publicSurveys" :key="survey.id"
                class="card bg-base-100 outline outline-base-300 hover:shadow-md transition-all duration-200 cursor-pointer hover:scale-[1.02]"
                @click="joinSurvey(survey.code)">
                <div class="p-5 card-body">
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <h3 class="card-title text-base-content">{{ survey.title }}</h3>
                      <p class="mt-2 text-sm text-base-content/70 line-clamp-2">{{ survey.description }}</p>
                      <div class="justify-start mt-3 card-actions">
                        <div class="badge badge-primary badge-outline">{{ survey.code }}</div>
                      </div>
                    </div>
                    <ChevronRight class="w-5 h-5 text-base-content/50" />
                  </div>
                </div>
              </div>
            </div>
          </ContentCard>

          <!-- No Public Surveys Message -->
          <ContentCard v-else-if="publicSurveys && publicSurveys.length === 0" title="📋 Survey Tersedia"
            subtitle="Tidak ada survey public yang tersedia saat ini" maxWidth="max-w-none">
            <div class="py-8 text-center">
              <div class="text-sm text-base-content/50">
                Saat ini tidak ada survey public yang dapat diakses. Silakan gunakan kode survey yang diberikan
                administrator.
              </div>
            </div>
          </ContentCard>
        </div>

        <!-- Right Column - Sticky Survey Code Entry Card -->
        <div class="lg:col-span-1">
          <div class="sticky top-8">
            <ContentCard title="🔐 Masuk ke Survey" subtitle="Masukkan kode survey untuk mengikuti survey"
              maxWidth="max-w-none">
              <form @submit.prevent="submitToken" class="space-y-6">
                <!-- Survey Code Input -->
                <div class="form-control">
                  <label class="label">
                    <span class="font-medium label-text">Kode Survey</span>
                  </label>
                  <div class="relative">
                    <input v-model="form.survey_code" type="text" placeholder="Masukkan kode survey Anda"
                      class="pr-12 w-full input input-bordered" :class="{ 'input-error': form.errors.survey_code }"
                      required />
                    <div class="flex absolute inset-y-0 right-0 items-center pr-3">
                      <Key class="w-5 h-5 text-base-content/50" />
                    </div>
                  </div>
                  <div v-if="form.errors.survey_code || true" class="whitespace-break-spaces label">
                    <span class="label-text-alt text-error">{{ form.errors.survey_code }}</span>
                  </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full btn btn-primary" :class="{ 'loading': form.processing }"
                  :disabled="form.processing || !form.survey_code">
                  <LogIn v-if="!form.processing" class="mr-2 w-5 h-5" />
                  {{ form.processing ? 'Memverifikasi...' : 'Masuk Survey' }}
                </button>
              </form>

              <!-- Info Card -->
              <div class="mt-6 alert alert-info">
                <Info class="w-5 h-5" />
                <div>
                  <h3 class="font-bold">Informasi Kode Survey</h3>
                  <div class="mt-1 text-xs">
                    Kode survey diberikan oleh administrator survey. Pastikan Anda memasukkan kode yang benar untuk
                    mengakses
                    survey yang dituju.
                  </div>
                </div>
              </div>

              <!-- Help Section -->
              <div class="mt-6 text-center">
                <p class="text-sm text-base-content/70">
                  Akses dashboard?
                  <Link :href="route('login')" class="link link-primary">Login sebagai administrator</Link>
                </p>
              </div>
            </ContentCard>
          </div>
        </div>
      </div>
    </div>
  </RawCenteredLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import { Key, LogIn, Info, ChevronRight } from 'lucide-vue-next'
import RawCenteredLayout from '@/Layouts/RawCenteredLayout.vue'
import ContentCard from '@/Components/ContentCard.vue'

// Props
const props = defineProps({
  publicSurveys: {
    type: Array,
    default: () => []
  }
})

// Route helper
const route = (name) => {
  const routes = {
    'login': '/login',
    'survey.enter': '/survey/enter',
  }
  return routes[name] || '/'
}

// Form handling
const form = useForm({
  survey_code: ''
})

// Submit survey code
const submitToken = () => {
  form.post(route('survey.enter'), {
    onSuccess: () => {
      // Redirect ke halaman respondent setelah sukses
    },
    onError: () => {
      // Error akan ditampilkan otomatis melalui form.errors
    }
  })
}

// Join survey directly by code
const joinSurvey = (surveyCode) => {
  form.survey_code = surveyCode
  submitToken()
}
</script>