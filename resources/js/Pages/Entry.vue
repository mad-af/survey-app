<template>

  <Head title="Masuk Survey" />

  <CenteredLayout title="🔐 Masuk ke Survey" subtitle="Masukkan kode survey untuk mengikuti survey"
    max-width="max-w-md">
    <!-- Token Input Form -->
    <form @submit.prevent="submitToken" class="space-y-6">
      <!-- Survey Code Input -->
      <div class="form-control">
        <label class="label">
          <span class="font-medium label-text">Kode Survey</span>
        </label>
        <div class="relative">
          <input v-model="form.survey_code" type="text" placeholder="Masukkan kode survey Anda"
            class="pr-12 w-full input input-bordered" :class="{ 'input-error': form.errors.survey_code }" required />
          <div class="flex absolute inset-y-0 right-0 items-center pr-3">
            <Key class="w-5 h-5 text-base-content/50" />
          </div>
        </div>
        <div v-if="form.errors.survey_code" class="label">
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
          Kode survey diberikan oleh administrator survey. Pastikan Anda memasukkan kode yang benar untuk mengakses
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
  </CenteredLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { Key, LogIn, Info } from 'lucide-vue-next'

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
</script>