<template>
  <!-- Modal -->
  <div v-if="isOpen" class="modal modal-open">
    <div class="w-96 max-w-md modal-box">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-semibold text-base-content">Ubah Password</h2>
          <button @click="$emit('close')" class="btn btn-sm btn-circle btn-ghost">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Current Password -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Password Saat Ini</span>
            </label>
            <div class="relative">
              <input 
                v-model="form.currentPassword"
                :type="showCurrentPassword ? 'text' : 'password'"
                class="pr-10 w-full input input-bordered input-sm"
                :class="{ 'input-error': errors.currentPassword }"
                placeholder="Masukkan password saat ini"
                required
              />
              <button 
                type="button"
                @click="showCurrentPassword = !showCurrentPassword"
                class="flex absolute inset-y-0 right-0 items-center pr-3"
              >
                <Eye v-if="!showCurrentPassword" class="w-4 h-4 text-base-content/50" />
                <EyeOff v-else class="w-4 h-4 text-base-content/50" />
              </button>
            </div>
            <label v-if="errors.currentPassword" class="label">
              <span class="label-text-alt text-error">{{ errors.currentPassword }}</span>
            </label>
          </div>

          <!-- New Password -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Password Baru</span>
            </label>
            <div class="relative">
              <input 
                v-model="form.newPassword"
                :type="showNewPassword ? 'text' : 'password'"
                class="pr-10 w-full input input-bordered input-sm"
                :class="{ 'input-error': errors.newPassword }"
                placeholder="Masukkan password baru"
                required
                minlength="8"
              />
              <button 
                type="button"
                @click="showNewPassword = !showNewPassword"
                class="flex absolute inset-y-0 right-0 items-center pr-3"
              >
                <Eye v-if="!showNewPassword" class="w-4 h-4 text-base-content/50" />
                <EyeOff v-else class="w-4 h-4 text-base-content/50" />
              </button>
            </div>
            <label v-if="errors.newPassword" class="label">
              <span class="label-text-alt text-error">{{ errors.newPassword }}</span>
            </label>
            <label class="label">
              <span class="label-text-alt text-base-content/60">Minimal 8 karakter</span>
            </label>
          </div>

          <!-- Confirm New Password -->
          <div class="form-control">
            <label class="label">
              <span class="label-text">Konfirmasi Password Baru</span>
            </label>
            <div class="relative">
              <input 
                v-model="form.confirmPassword"
                :type="showConfirmPassword ? 'text' : 'password'"
                class="pr-10 w-full input input-bordered input-sm"
                :class="{ 'input-error': errors.confirmPassword }"
                placeholder="Konfirmasi password baru"
                required
              />
              <button 
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="flex absolute inset-y-0 right-0 items-center pr-3"
              >
                <Eye v-if="!showConfirmPassword" class="w-4 h-4 text-base-content/50" />
                <EyeOff v-else class="w-4 h-4 text-base-content/50" />
              </button>
            </div>
            <label v-if="errors.confirmPassword" class="label">
              <span class="label-text-alt text-error">{{ errors.confirmPassword }}</span>
            </label>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 pt-4">
            <button 
              type="button" 
              @click="$emit('close')" 
              class="flex-1 btn btn-outline btn-sm"
              :disabled="loading"
            >
              Batal
            </button>
            <button 
              type="submit" 
              class="flex-1 btn btn-primary btn-sm"
              :disabled="loading || !isFormValid"
            >
              <span v-if="loading" class="loading loading-spinner loading-sm"></span>
              {{ loading ? 'Mengubah...' : 'Ubah Password' }}
            </button>
          </div>
        </form>
      </div>
      
      <!-- Modal backdrop -->
      <form method="dialog" class="modal-backdrop">
        <button @click="$emit('close')">close</button>
      </form>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { X, Eye, EyeOff } from 'lucide-vue-next'

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['close', 'submit'])

// State
const loading = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const form = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const errors = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const isFormValid = computed(() => {
  return form.value.currentPassword && 
         form.value.newPassword && 
         form.value.confirmPassword &&
         form.value.newPassword === form.value.confirmPassword &&
         form.value.newPassword.length >= 8
})

const validateForm = () => {
  errors.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }

  if (!form.value.currentPassword) {
    errors.value.currentPassword = 'Password saat ini harus diisi'
  }

  if (!form.value.newPassword) {
    errors.value.newPassword = 'Password baru harus diisi'
  } else if (form.value.newPassword.length < 8) {
    errors.value.newPassword = 'Password baru minimal 8 karakter'
  }

  if (!form.value.confirmPassword) {
    errors.value.confirmPassword = 'Konfirmasi password harus diisi'
  } else if (form.value.newPassword !== form.value.confirmPassword) {
    errors.value.confirmPassword = 'Konfirmasi password tidak cocok'
  }

  return !Object.values(errors.value).some(error => error)
}

const resetForm = () => {
  form.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
  errors.value = {
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  }
  showCurrentPassword.value = false
  showNewPassword.value = false
  showConfirmPassword.value = false
}

const submitForm = async () => {
  if (!validateForm()) return

  loading.value = true
  
  try {
    await emit('submit', {
      currentPassword: form.value.currentPassword,
      newPassword: form.value.newPassword,
      confirmPassword: form.value.confirmPassword
    })
    resetForm()
  } catch (error) {
    console.error('Error changing password:', error)
  } finally {
    loading.value = false
  }
}

// Watch for drawer open/close
watch(() => props.isOpen, (newValue) => {
  if (!newValue) {
    resetForm()
  }
})
</script>