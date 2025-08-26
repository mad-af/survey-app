<template>
  <Head title="Login" />
  
  <CenteredLayout 
    title="🔐 Login"
    subtitle="Masuk ke dashboard administrator"
    max-width="max-w-md"
  >
     <!-- Login Form -->
     <form @submit.prevent="submitLogin" class="space-y-6">
       <!-- Email Input -->
       <div class="form-control">
         <label class="label">
           <span class="label-text font-medium">Email</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.email" 
             type="email" 
             placeholder="Masukkan email Anda" 
             class="input input-bordered w-full pr-12"
             :class="{ 'input-error': form.errors.email }"
             required
           />
           <div class="absolute inset-y-0 right-0 flex items-center pr-3">
             <Mail class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
         <div v-if="form.errors.email" class="label">
           <span class="label-text-alt text-error">{{ form.errors.email }}</span>
         </div>
       </div>

       <!-- Password Input -->
       <div class="form-control">
         <label class="label">
           <span class="label-text font-medium">Password</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.password" 
             type="password" 
             placeholder="Masukkan password Anda" 
             class="input input-bordered w-full pr-12"
             :class="{ 'input-error': form.errors.password }"
             required
           />
           <div class="absolute inset-y-0 right-0 flex items-center pr-3">
             <Lock class="w-5 h-5 text-base-content/50" />
           </div>
         </div>
         <div v-if="form.errors.password" class="label">
           <span class="label-text-alt text-error">{{ form.errors.password }}</span>
         </div>
       </div>

       <!-- Submit Button -->
       <button 
         type="submit" 
         class="btn btn-primary w-full"
         :class="{ 'loading': form.processing }"
         :disabled="form.processing || !form.email || !form.password"
       >
         <LogIn v-if="!form.processing" class="w-5 h-5 mr-2" />
         {{ form.processing ? 'Masuk...' : 'Login' }}
       </button>
     </form>

     <!-- Info Card -->
     <div class="alert alert-info mt-6">
       <Info class="w-5 h-5" />
       <div>
         <h3 class="font-bold">Akun Demo</h3>
         <div class="text-xs mt-1">
           <strong>Email:</strong> admin@survey.com<br>
           <strong>Password:</strong> password123<br>
           <span class="text-info-content/70 mt-1 block">Gunakan akun demo di atas untuk mencoba website ini.</span>
         </div>
       </div>
     </div>

     <!-- Help Section -->
     <div class="text-center mt-6">
       <p class="text-sm text-base-content/70">
         Ingin mengikuti survey? 
         <Link :href="route('entry')" class="link link-primary">Masuk dengan token</Link>
       </p>
     </div>
  </CenteredLayout>
</template>

<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { Mail, Lock, LogIn, Info } from 'lucide-vue-next'

// Route helper
const route = (name) => {
  const routes = {
    'entry': '/entry',
    'login': '/login'
  }
  return routes[name] || '/'
}

// Form handling
const form = useForm({
  email: '',
  password: ''
})

// Submit login
const submitLogin = () => {
  form.post(route('login'), {
    onSuccess: () => {
      // Redirect akan ditangani oleh backend
    },
    onError: () => {
      // Error akan ditampilkan otomatis melalui form.errors
    }
  })
}
</script>