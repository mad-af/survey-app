<template>
  <!-- Theme Selector - Fixed Position Bottom Right -->
  <div class="fixed bottom-4 right-4 z-50">
    <div class="dropdown dropdown-top dropdown-end">
      <div tabindex="0" role="button" class="btn btn-lg btn-circle btn-ghost bg-base-100/80 backdrop-blur-sm shadow-lg hover:shadow-xl transition-all duration-200">
        <Palette class="size-5.5 text-base-content/70" />
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

onMounted(() => {
  const savedTheme = localStorage.getItem('theme') || 'light'
  document.documentElement.setAttribute('data-theme', savedTheme)
})
</script>