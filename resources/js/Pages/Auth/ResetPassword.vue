<template>
  <Head title="Reset Password" />
  
  <CenteredLayout 
    title="🔐 Reset Password"
    subtitle="Masukkan password baru Anda"
    max-width="max-w-md"
  >
     <!-- Reset Password Form -->
     <form @submit.prevent="submitResetPassword" class="space-y-6">
       <!-- Email Input (Hidden/Readonly) -->
       <div class="form-control">
         <label class="label">
           <span class="font-medium label-text">Email</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.email" 
             type="email" 
             class="pr-12 w-full input input-bordered bg-base-200"
             readonly
           />
           <div class="flex absolute inset-y-0 right-0 items-center pr-3">
             <Mail class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
       </div>

       <!-- Password Input -->
       <div class="form-control">
         <label class="label">
           <span class="font-medium label-text">Password Baru</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.password" 
             type="password" 
             placeholder="Masukkan password baru" 
             class="pr-12 w-full input input-bordered"
             :class="{ 'input-error': form.errors.password }"
             required
           />
           <div class="flex absolute inset-y-0 right-0 items-center pr-3">
             <Lock class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
         <div v-if="form.errors.password" class="label">
           <span class="label-text-alt text-error">{{ form.errors.password }}</span>
         </div>
       </div>

       <!-- Confirm Password Input -->
       <div class="form-control">
         <label class="label">
           <span class="font-medium label-text">Konfirmasi Password</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.password_confirmation" 
             type="password" 
             placeholder="Konfirmasi password baru" 
             class="pr-12 w-full input input-bordered"
             :class="{ 'input-error': form.errors.password_confirmation }"
             required
           />
           <div class="flex absolute inset-y-0 right-0 items-center pr-3">
             <Lock class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
         <div v-if="form.errors.password_confirmation" class="label">
           <span class="label-text-alt text-error">{{ form.errors.password_confirmation }}</span>
         </div>
       </div>

       <!-- Submit Button -->
       <button 
         type="submit" 
         class="w-full btn btn-primary"
         :class="{ 'loading': form.processing }"
         :disabled="form.processing || !form.password || !form.password_confirmation"
       >
         <Key v-if="!form.processing" class="mr-2 w-5 h-5" />
         {{ form.processing ? 'Mereset...' : 'Reset Password' }}
       </button>
     </form>

     <!-- Info Card -->
     <div class="mt-6 alert alert-info">
       <Info class="w-5 h-5" />
       <div>
         <h3 class="font-bold">Keamanan Password</h3>
         <div class="mt-1 text-xs">
           <span class="block text-info-content/70">
             Gunakan password yang kuat dengan minimal 8 karakter, 
             kombinasi huruf besar, kecil, angka, dan simbol.
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
import { Mail, Lock, Key, Info } from 'lucide-vue-next'

// Props
const props = defineProps({
  token: String,
  email: String
})

// Route helper
const route = (name) => {
  const routes = {
    'login': '/login'
  }
  return routes[name] || '/'
}

// Form handling
const form = useForm({
  token: props.token || '',
  email: props.email || '',
  password: '',
  password_confirmation: ''
})

// Submit reset password
const submitResetPassword = () => {
  form.post('/reset-password', {
    onSuccess: () => {
      // Redirect will be handled by the backend
    },
    onError: (errors) => {
      console.log('Reset password failed:', errors)
    }
  })
}
</script>