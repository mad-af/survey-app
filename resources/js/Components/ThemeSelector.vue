<template>
  <!-- Theme Selector - Fixed Position Bottom Right -->
  <div class="fixed bottom-4 right-4 z-50">
    <div class="dropdown dropdown-top dropdown-end">
      <div tabindex="0" role="button" class="btn btn-lg btn-circle btn-primary shadow-lg hover:shadow-xl transition-all duration-200">
        <Palette class="size-7" />
      </div>
      <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-2xl bg-base-100 rounded-box w-52 max-h-96 overflow-y-auto border border-base-300">
        <li class="menu-title">
          <span class="text-xs font-semibold text-base-content/70">Pilih Tema</span>
        </li>
        <li v-for="theme in themes" :key="theme">
          <a @click="changeTheme(theme)" class="capitalize text-sm hover:bg-primary hover:text-primary-content transition-colors">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 rounded-full" :class="getThemePreview(theme)"></div>
              {{ theme }}
            </div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { Palette } from 'lucide-vue-next'

// Theme management - Survey-friendly themes only
const themes = [
  'light', 'corporate', 'emerald', 'garden', 'lofi', 'pastel', 
  'wireframe', 'business', 'winter', 'cupcake', 'aqua'
]

const changeTheme = (theme) => {
  document.documentElement.setAttribute('data-theme', theme)
  localStorage.setItem('theme', theme)
}

// Theme preview colors - Survey-friendly themes only
const getThemePreview = (theme) => {
  const themeColors = {
    'light': 'bg-white border border-gray-300',
    'corporate': 'bg-blue-600',
    'emerald': 'bg-emerald-500',
    'garden': 'bg-green-500',
    'lofi': 'bg-gray-400',
    'pastel': 'bg-purple-300',
    'wireframe': 'bg-gray-100 border border-gray-400',
    'business': 'bg-blue-800',
    'winter': 'bg-blue-100',
    'cupcake': 'bg-pink-200',
    'aqua': 'bg-cyan-400'
  }
  return themeColors[theme] || 'bg-gray-300'
}

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  document.documentElement.setAttribute('data-theme', savedTheme)
})
</script>