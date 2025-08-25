<template>
  <div class="min-h-screen bg-base-200">


    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center p-4 lg:p-8">
      <div class="w-full max-w-4xl mx-auto">
        <!-- Content Card -->
        <div class="card bg-base-100 shadow-xl border border-base-300">
          <div class="card-body p-6 lg:p-8">
            <!-- Breadcrumb jika ada -->
            <div v-if="breadcrumbs && breadcrumbs.length > 0" class="breadcrumbs text-sm mb-4">
              <ul>
                <li v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                  <a v-if="breadcrumb.url" :href="breadcrumb.url" class="gap-1">
                    <component :is="breadcrumb.icon" v-if="breadcrumb.icon" class="w-4 h-4" />
                    {{ breadcrumb.title }}
                  </a>
                  <span v-else class="gap-1">
                    <component :is="breadcrumb.icon" v-if="breadcrumb.icon" class="w-4 h-4" />
                    {{ breadcrumb.title }}
                  </span>
                </li>
              </ul>
            </div>
            
            <!-- Page Title -->
            <div v-if="title" class="flex items-center gap-3 mb-6">
              <component :is="titleIcon" v-if="titleIcon" class="w-8 h-8 text-primary" />
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-base-content">{{ title }}</h1>
                <p v-if="subtitle" class="text-base-content/70 mt-1">{{ subtitle }}</p>
              </div>
            </div>
            
            <!-- Slot untuk konten utama -->
            <div class="space-y-6">
              <slot />
            </div>

          </div>
        </div>
      </div>
    </main>

    <!-- Theme Selector - Fixed Position Bottom Right -->
    <div class="fixed bottom-4 right-4 z-50">
      <div class="dropdown dropdown-top dropdown-end">
        <div tabindex="0" role="button" class="btn btn-circle btn-primary shadow-lg hover:shadow-xl transition-all duration-200">
          <Palette class="w-5 h-5" />
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
   
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import {
  LayoutGrid,
  Home,
  FileText,
  BarChart3,
  Palette,
  User,
  Settings,
  LogOut,
  Heart,
  Zap,
  Sparkles,
  Paintbrush,
  Star
} from 'lucide-vue-next'

// Props
const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  titleIcon: {
    type: [String, Object],
    default: null
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  },
  appName: {
    type: String,
    default: 'Survey App'
  }
})

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
