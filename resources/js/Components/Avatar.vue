<template>
  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
    <img 
      v-if="src && !imageError" 
      :src="src" 
      :alt="alt || 'Avatar'" 
      class="w-8 h-8 rounded-full object-cover" 
      @error="handleImageError" 
    />
    <div 
      v-else 
      class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-semibold text-xs"
    >
      {{ initials }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: null
  },
  name: {
    type: String,
    default: ''
  },
  alt: {
    type: String,
    default: 'Avatar'
  }
})

const imageError = ref(false)

const initials = computed(() => {
  if (!props.name) return '??'
  const words = props.name.trim().split(' ')
  if (words.length === 1) {
    return words[0].substring(0, 2).toUpperCase()
  }
  return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase()
})

const handleImageError = () => {
  imageError.value = true
}
</script>