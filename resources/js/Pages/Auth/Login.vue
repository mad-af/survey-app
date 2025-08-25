<template>
  <div class="min-h-screen bg-gradient-to-br from-primary/10 to-secondary/10 flex items-center justify-center p-4">
    <div class="card w-full max-w-md bg-base-100 shadow-2xl">
      <div class="card-body">
        <!-- Header -->
        <div class="text-center mb-6">
          <div class="avatar placeholder mb-4">
            <div class="bg-primary text-primary-content rounded-full w-16">
              <span class="text-2xl font-bold">S</span>
            </div>
          </div>
          <h1 class="text-2xl font-bold">Survey App</h1>
          <p class="text-base-content/60">Masuk ke dashboard admin</p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="login" class="space-y-4">
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Email</span>
            </label>
            <div class="relative">
              <input 
                type="email" 
                placeholder="admin@example.com" 
                class="input input-bordered w-full pl-10" 
                v-model="form.email"
                :class="{ 'input-error': errors.email }"
                required
              />
              <Mail class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-base-content/40" />
            </div>
            <label v-if="errors.email" class="label">
              <span class="label-text-alt text-error">{{ errors.email }}</span>
            </label>
          </div>

          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Password</span>
            </label>
            <div class="relative">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="••••••••" 
                class="input input-bordered w-full pl-10 pr-10" 
                v-model="form.password"
                :class="{ 'input-error': errors.password }"
                required
              />
              <Lock class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-base-content/40" />
              <button 
                type="button"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-base-content/40 hover:text-base-content"
                @click="showPassword = !showPassword"
              >
                <Eye v-if="showPassword" class="w-4 h-4" />
                <EyeOff v-else class="w-4 h-4" />
              </button>
            </div>
            <label v-if="errors.password" class="label">
              <span class="label-text-alt text-error">{{ errors.password }}</span>
            </label>
          </div>

          <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
              <input type="checkbox" class="checkbox checkbox-primary" v-model="form.remember" />
              <span class="label-text">Ingat saya</span>
            </label>
          </div>

          <div class="form-control mt-6">
            <button 
              type="submit" 
              class="btn btn-primary" 
              :class="{ 'loading': isLoading }"
              :disabled="isLoading"
            >
              <LogIn v-if="!isLoading" class="w-4 h-4" />
              Masuk
            </button>
          </div>
        </form>

        <!-- Divider -->
        <div class="divider">atau</div>

        <!-- Alternative Actions -->
        <div class="space-y-3">
          <a href="/survey-access" class="btn btn-outline btn-block">
            <Key class="w-4 h-4" />
            Akses Survey dengan Token
          </a>
          
          <div class="text-center">
            <a href="#" class="link link-hover text-sm">Lupa password?</a>
          </div>
        </div>

        <!-- Demo Credentials -->
        <div class="alert alert-info mt-4">
          <Info class="w-4 h-4" />
          <div class="text-sm">
            <strong>Demo:</strong><br>
            Email: admin@example.com<br>
            Password: password
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
      <p class="text-sm text-base-content/60 text-center">
        © 2024 Survey App. Semua hak dilindungi.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { 
  Mail, Lock, Eye, EyeOff, LogIn, Key, Info
} from 'lucide-vue-next'

const showPassword = ref(false)
const isLoading = ref(false)

const form = reactive({
  email: '',
  password: '',
  remember: false
})

const errors = reactive({
  email: '',
  password: ''
})

const login = async () => {
  // Reset errors
  errors.email = ''
  errors.password = ''
  
  // Basic validation
  if (!form.email) {
    errors.email = 'Email harus diisi'
    return
  }
  
  if (!form.password) {
    errors.password = 'Password harus diisi'
    return
  }
  
  if (form.password.length < 6) {
    errors.password = 'Password minimal 6 karakter'
    return
  }
  
  isLoading.value = true
  
  try {
    // TODO: Implement actual login API call
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
    
    // Demo login - check credentials
    if (form.email === 'admin@example.com' && form.password === 'password') {
      // Success - redirect to dashboard
      window.location.href = '/dashboard'
    } else {
      errors.email = 'Email atau password salah'
    }
  } catch (error) {
    console.error('Login error:', error)
    errors.email = 'Terjadi kesalahan saat login'
  } finally {
    isLoading.value = false
  }
}
</script>