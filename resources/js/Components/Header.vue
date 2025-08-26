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
    <div class="flex-none gap-2">
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
import { ref, computed } from 'vue'
import { Menu } from 'lucide-vue-next'

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
</script>