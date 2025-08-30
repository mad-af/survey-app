<template>
  <Head title="Lupa Password" />
  
  <CenteredLayout 
    title="🔑 Lupa Password"
    subtitle="Masukkan email Anda untuk reset password"
    max-width="max-w-md"
  >
     <!-- Status Message -->
     <div v-if="status" class="mb-4 alert alert-success">
       <CheckCircle class="w-5 h-5" />
       <span>{{ status }}</span>
     </div>

     <!-- Forgot Password Form -->
     <form @submit.prevent="submitForgotPassword" class="space-y-6">
       <!-- Email Input -->
       <div class="form-control">
         <label class="label">
           <span class="font-medium label-text">Email</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.email" 
             type="email" 
             placeholder="Masukkan email Anda" 
             class="pr-12 w-full input input-bordered"
             :class="{ 'input-error': form.errors.email }"
             required
           />
           <div class="flex absolute inset-y-0 right-0 items-center pr-3">
             <Mail class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
         <div v-if="form.errors.email" class="label">
           <span class="label-text-alt text-error">{{ form.errors.email }}</span>
         </div>
       </div>

       <!-- Submit Button -->
       <button 
         type="submit" 
         class="w-full btn btn-primary"
         :class="{ 'loading': form.processing }"
         :disabled="form.processing || !form.email"
       >
         <Send v-if="!form.processing" class="mr-2 w-5 h-5" />
         {{ form.processing ? 'Mengirim...' : 'Kirim Link Reset Password' }}
       </button>
     </form>

     <!-- Info Card -->
     <div class="mt-6 alert alert-info">
       <Info class="w-5 h-5" />
       <div>
         <h3 class="font-bold">Informasi</h3>
         <div class="mt-1 text-xs">
           <span class="block text-info-content/70">
             Kami akan mengirimkan link reset password ke email Anda. 
             Periksa folder inbox dan spam.
           </span>
         </div>
       </div>
     </div>

     <!-- Back to Login -->
     <div class="mt-6 text-center">
       <p class="text-sm text-base-content/70">
         Sudah ingat password? 
         <Link :href="route('login')" class="link link-primary">Kembali ke Login</Link>
       </p>
     </div>
  </CenteredLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { Mail, Send, Info, CheckCircle } from 'lucide-vue-next'

// Props
defineProps({
  status: String
})

// Route helper
const route = (name) => {
  const routes = {
    'login': '/login',
    'password.request': '/forgot-password'
  }
  return routes[name] || '/'
}

// Form handling
const form = useForm({
  email: ''
})

// Submit forgot password
const submitForgotPassword = () => {
  form.post('/forgot-password', {
    onSuccess: () => {
      // Success message will be handled by status prop
    },
    onError: (errors) => {
      console.log('Forgot password failed:', errors)
    }
  })
}
</script>