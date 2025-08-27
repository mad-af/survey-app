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

       <!-- Password Input -->
       <div class="form-control">
         <label class="label">
           <span class="font-medium label-text">Password</span>
         </label>
         <div class="relative">
           <input 
             v-model="form.password" 
             type="password" 
             placeholder="Masukkan password Anda" 
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

       <!-- Submit Button -->
       <button 
         type="submit" 
         class="w-full btn btn-primary"
         :class="{ 'loading': form.processing }"
         :disabled="form.processing || !form.email || !form.password"
       >
         <LogIn v-if="!form.processing" class="mr-2 w-5 h-5" />
         {{ form.processing ? 'Masuk...' : 'Login' }}
       </button>
     </form>

     <!-- Info Card -->
     <div class="mt-6 alert alert-info">
       <Info class="w-5 h-5" />
       <div>
         <h3 class="font-bold">Akun Demo</h3>
         <div class="mt-1 text-xs">
           <strong>Email:</strong> admin@survey.com<br>
           <strong>Password:</strong> password<br>
           <span class="block mt-1 text-info-content/70">Gunakan akun demo di atas untuk mencoba website ini.</span>
         </div>
       </div>
     </div>

     <!-- Help Section -->
     <div class="mt-6 text-center">
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