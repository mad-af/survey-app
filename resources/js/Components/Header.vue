<template>
  <div class="p-3.5 border-b navbar bg-base-100 border-base-300">
    <div class="flex-none lg:hidden">
      <label for="drawer-toggle" class="btn btn-square btn-ghost btn-sm">
        <Menu class="w-4 h-4" />
      </label>
    </div>
    <div class="flex-1">
      <h1 class="text-lg font-semibold text-base-content">{{ pageTitle }}</h1>
    </div>
    <div class="flex gap-2 items-center">
      <!-- Theme Selector -->
      <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
          <Palette class="size-5 text-base-content/70" />
        </div>
        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-2xl bg-base-100/90 backdrop-blur-md rounded-box w-52 max-h-96 overflow-y-auto border border-base-300/50">
          <li class="menu-title">
            <span class="text-xs font-semibold text-base-content/70">Pilih Tema</span>
          </li>
          <li v-for="theme in themes" :key="theme">
            <a @click="changeTheme(theme)" class="text-sm capitalize transition-colors">
              {{ theme }}
            </a>
          </li>
        </ul>
      </div>

      <!-- User Profile -->
      <div class="dropdown dropdown-end">
        <div tabindex="0" role="button" class="px-2 py-1 h-auto min-h-0 btn btn-ghost btn-sm">
          <div class="flex gap-2 items-center">
            <Avatar :src="profileImage" :name="userName" alt="Profile" />
            <div class="hidden text-left sm:block">
              <div class="text-sm font-medium text-base-content">{{ userName }}</div>
              <div class="text-xs text-base-content/60">{{ userRole }}</div>
            </div>
          </div>
        </div>
        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-2xl bg-base-100/90 backdrop-blur-md rounded-box w-52 border border-base-300/50">
          <li class="menu-title">
            <span class="text-xs font-semibold text-base-content/70">Akun</span>
          </li>
          <li>
            <a @click="openChangePasswordModal" class="text-sm transition-colors">
              <Key class="w-4 h-4" />
              Ubah Password
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <Breadcrumb />

  <!-- Change Password Modal -->
  <ChangePasswordModal 
    :is-open="isChangePasswordModalOpen"
    @close="closeChangePasswordModal"
    @submit="handleChangePassword"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { Key, Menu, Palette } from 'lucide-vue-next'
import Avatar from './Avatar.vue'
import Breadcrumb from './Breadcrumb.vue'
import ChangePasswordModal from './ChangePasswordModal.vue'

// Props
const props = defineProps({
  userName: {
    type: String,
    default: 'Esther H.'
  },
  userRole: {
    type: String,
    default: 'Designer'
  },
  profileImage: {
    type: String,
    default: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=32&h=32&fit=crop&crop=face'
  },
  pageTitle: {
    type: String,
    default: 'My Dashboard'
  }
})

// State
const isChangePasswordModalOpen = ref(false)

// Theme management - Survey-friendly themes only
const themes = [
  'light', 'corporate', 'emerald', 'garden', 'lofi', 'pastel', 
  'wireframe', 'business', 'winter', 'cupcake', 'aqua'
]

// Computed (userInitials removed as it's now handled by Avatar component)

// Methods (handleImageError removed as it's now handled by Avatar component)

const changeTheme = (theme) => {
  document.documentElement.setAttribute('data-theme', theme)
  localStorage.setItem('theme', theme)
}

const openChangePasswordModal = () => {
  isChangePasswordModalOpen.value = true
}

const closeChangePasswordModal = () => {
  isChangePasswordModalOpen.value = false
}

const handleChangePassword = async (passwordData) => {
  try {
    router.post('/dashboard/change-password', {
      current_password: passwordData.currentPassword,
      new_password: passwordData.newPassword,
      new_password_confirmation: passwordData.confirmPassword
    }, {
      onSuccess: () => {
        // Controller will handle redirect to login page
        closeChangePasswordModal()
      },
      onError: (errors) => {
        // Handle validation errors
        if (errors) {
          let errorMessage = 'Terjadi kesalahan:\n'
          Object.values(errors).forEach(errorArray => {
            if (Array.isArray(errorArray)) {
              errorArray.forEach(error => {
                errorMessage += `- ${error}\n`
              })
            } else {
              errorMessage += `- ${errorArray}\n`
            }
          })
          alert(errorMessage)
        } else {
          alert('Terjadi kesalahan saat mengubah password')
        }
      }
    })
  } catch (error) {
    console.error('Error changing password:', error)
    alert('Terjadi kesalahan saat mengubah password')
  }
}

// Lifecycle
onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  document.documentElement.setAttribute('data-theme', savedTheme)
})
</script>