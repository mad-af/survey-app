<template>
  <div class="navbar bg-base-100 p-3.5 border-b border-base-300">
    <div class="flex-none lg:hidden">
      <label for="drawer-toggle" class="btn btn-square btn-ghost btn-sm">
        <Menu class="w-4 h-4" />
      </label>
    </div>
    <div class="flex-1">
      <h1 class="text-lg font-semibold text-base-content">My Dashboard</h1>
    </div>
    <div class="flex items-center gap-2">
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
            <a @click="changeTheme(theme)" class="capitalize text-sm transition-colors">
              {{ theme }}
            </a>
          </li>
        </ul>
      </div>

      <!-- User Profile -->
      <div tabindex="0" role="button" class="btn btn-ghost btn-sm px-2 py-1 h-auto min-h-0">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
            <img v-if="profileImage && !imageError" :src="profileImage" alt="Profile" class="w-8 h-8 rounded-full object-cover" @error="handleImageError" />
            <div v-else class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-semibold text-xs">{{ userInitials }}</div>
          </div>
          <div class="text-left hidden sm:block">
            <div class="text-sm font-medium text-base-content">{{ userName }}</div>
            <div class="text-xs text-base-content/60">{{ userRole }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Menu, Palette } from 'lucide-vue-next'

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
  }
})

// State
const imageError = ref(false)

// Theme management - Survey-friendly themes only
const themes = [
  'light', 'corporate', 'emerald', 'garden', 'lofi', 'pastel', 
  'wireframe', 'business', 'winter', 'cupcake', 'aqua'
]

// Computed
const userInitials = computed(() => {
  return props.userName
    .split(' ')
    .map(name => name.charAt(0))
    .join('')
    .toUpperCase()
})

// Methods
const handleImageError = () => {
  imageError.value = true
}

const changeTheme = (theme) => {
  document.documentElement.setAttribute('data-theme', theme)
  localStorage.setItem('theme', theme)
}

// Lifecycle
onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  document.documentElement.setAttribute('data-theme', savedTheme)
})
</script>