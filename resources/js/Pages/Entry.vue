<template>
  <Head title="Masuk Survey" />
  
  <CenteredLayout 
    title="🔐 Masuk ke Survey"
    subtitle="Masukkan token akses untuk mengikuti survey"
    max-width="max-w-md"
  >
     <!-- Token Input Form -->
     <form @submit.prevent="submitToken" class="space-y-6">
       <!-- Token Input -->
       <div class="form-control">
         <label class="label">
           <span class="label-text font-medium">Token Akses</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.token" 
             type="text" 
             placeholder="Masukkan token survey Anda" 
             class="input input-bordered w-full pr-12"
             :class="{ 'input-error': form.errors.token }"
             required
           />
           <div class="absolute inset-y-0 right-0 flex items-center pr-3">
             <Key class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
         <div v-if="form.errors.token" class="label">
           <span class="label-text-alt text-error">{{ form.errors.token }}</span>
         </div>
       </div>

       <!-- Submit Button -->
       <button 
         type="submit" 
         class="btn btn-primary w-full"
         :class="{ 'loading': form.processing }"
         :disabled="form.processing || !form.token"
       >
         <LogIn v-if="!form.processing" class="w-5 h-5 mr-2" />
         {{ form.processing ? 'Memverifikasi...' : 'Masuk Survey' }}
       </button>
     </form>

     <!-- Info Card -->
     <div class="alert alert-info mt-6">
       <Info class="w-5 h-5" />
       <div>
         <h3 class="font-bold">Informasi Token</h3>
         <div class="text-xs mt-1">
           Token akses diberikan oleh administrator survey. Pastikan Anda memasukkan token yang benar untuk mengakses survey yang dituju.
         </div>
       </div>
     </div>

     <!-- Help Section -->
     <div class="text-center mt-6">
       <p class="text-sm text-base-content/70">
         Tidak memiliki token? 
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
    'survey.enter': '/survey/enter'
  }
  return routes[name] || '/'
}

// Form handling
const form = useForm({
  token: ''
})

// Submit token
const submitToken = () => {
  form.post(route('survey.enter'), {
    onSuccess: () => {
      // Redirect akan ditangani oleh backend
    },
    onError: () => {
      // Error akan ditampilkan otomatis melalui form.errors
    }
  })
}
</script>