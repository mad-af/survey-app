<template>
  <Head title="AKUSUKA - Aplikasi kuisioner dan survey kabupaten" />
  
  <div class="min-h-screen bg-base-100">
    <!-- Navigation Header -->
    <header class="sticky top-0 z-50 px-4 border-b shadow-sm navbar bg-base-100 border-base-300">
      <div class="navbar-start">
        <div class="dropdown">
          <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
            <Menu class="w-5 h-5" />
          </div>
          <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
            <li><a href="#surveys">Survey Aktif</a></li>
            <li><a href="#about">Tentang Kami</a></li>
            <li><a href="/entry">Ikuti Survey</a></li>
            <li><a href="/login">Login Admin</a></li>
          </ul>
        </div>
        <a class="text-xl font-bold btn btn-ghost">
          <!-- <FileText class="w-6 h-6 text-primary" />
          SurveyApp -->
          <div class="flex gap-2 items-center">
            <img src="/beraksi.webp" alt="AKUSUKA Logo" class="h-6 lg:h-8" />
            <!-- <div class="hidden sm:block">
              <div class="text-lg font-bold text-primary">AKUSUKA</div>
              <div class="text-xs text-base-content/70">Partisipasi Mudah Manfaat Nyata</div>
            </div> -->
          </div>
        </a>
      </div>
      
      <div class="hidden navbar-center lg:flex">
        <ul class="px-1 menu menu-horizontal">
          <li><a href="#surveys" class="hover:text-primary">Survey Aktif</a></li>
          <li><a href="#about" class="hover:text-primary">Tentang Kami</a></li>
          <!-- <li><a href="/entry" class="hover:text-primary">Ikuti Survey</a></li> -->
        </ul>
      </div>
      
      <div class="navbar-end">
        <div class="flex gap-2 items-center">
          <!-- <a href="/entry" class="gap-2 btn btn-sm btn-primary">
            <Play class="w-4 h-4" />
            Ikuti Survey
          </a> -->
          <a href="/login" class="gap-2 btn btn-sm btn-ghost">
            <LogIn class="w-4 h-4" />
            Login
          </a>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero min-h-[60vh] bg-gradient-to-br from-primary/10 to-secondary/10">
      <div class="text-center hero-content">
        <div class="max-w-4xl">
          <h1 class="mb-6 text-5xl font-bold">
            <span class="text-primary">AKUSUKA</span> Aplikasi kuisioner dan survey kabupaten
          </h1>
          <p class="mb-4 text-2xl font-semibold text-secondary">
            Partisipasi Mudah Manfaat Nyata
          </p>
          <p class="mb-8 text-xl text-base-content/70">
            Platform survey modern untuk kabupaten yang memudahkan partisipasi masyarakat dalam memberikan masukan dan pendapat.
          </p>
          <div class="flex flex-col gap-4 justify-center sm:flex-row">
            <a href="/entry" class="gap-2 btn btn-primary btn-lg">
              <Play class="w-5 h-5" />
              Ikuti Survey
            </a>
            <!-- <a href="/login" class="gap-2 btn btn-outline btn-lg">
              <LogIn class="w-5 h-5" />
              Login Admin
            </a> -->
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 bg-base-200">
      <div class="container px-4 mx-auto">
        <div class="w-full shadow stats stats-vertical lg:stats-horizontal">
          <div class="stat bg-base-100">
            <div class="stat-figure text-primary">
              <Users class="w-8 h-8" />
            </div>
            <div class="stat-title">Total Responden</div>
            <div class="stat-value text-primary">{{ totalRespondents.toLocaleString() }}</div>
            <div class="stat-desc text-success">↗︎ Terus bertambah</div>
          </div>
          
          <div class="stat bg-base-100">
            <div class="stat-figure text-secondary">
              <FileText class="w-8 h-8" />
            </div>
            <div class="stat-title">Survey Aktif</div>
            <div class="stat-value text-secondary">{{ activeSurveys.length }}</div>
            <div class="stat-desc text-info">Siap diikuti</div>
          </div>
          
          <div class="stat bg-base-100">
            <div class="stat-figure text-accent">
              <BarChart3 class="w-8 h-8" />
            </div>
            <div class="stat-title">Total Respons</div>
            <div class="stat-value text-accent">{{ totalResponses.toLocaleString() }}</div>
            <div class="stat-desc text-success">Dari semua survey</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Active Surveys Section -->
    <section id="surveys" class="py-16">
      <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
          <h2 class="mb-4 text-4xl font-bold">Survey Aktif</h2>
          <p class="text-xl text-base-content/70">
            Ikuti survey yang sedang berlangsung dan berikan pendapat Anda
          </p>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex justify-center items-center py-12">
          <span class="loading loading-spinner loading-lg"></span>
          <span class="ml-3 text-lg">Memuat survey...</span>
        </div>

        <!-- Surveys Grid -->
        <div v-else-if="activeSurveys.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="survey in activeSurveys" :key="survey.id" class="shadow-lg transition-shadow card bg-base-100 hover:shadow-xl">
            <div class="card-body">
              <h3 class="mb-2 text-lg card-title">{{ survey.title }}</h3>
              <p class="mb-4 text-base-content/70 line-clamp-3">{{ survey.description }}</p>
              
              <div class="flex gap-2 items-center mb-4 text-sm text-base-content/60">
                <Clock class="w-4 h-4" />
                <span>Estimasi: {{ survey.estimated_duration || '5-10' }} menit</span>
              </div>
              
              <div class="flex gap-2 items-center mb-4 text-sm text-base-content/60">
                <Users class="w-4 h-4" />
                <span>{{ survey.responses_count || 0 }} responden</span>
              </div>
              
              <div class="justify-end card-actions">
                <a href="/entry" class="gap-2 btn btn-primary">
                  <Play class="w-4 h-4" />
                  Ikuti Survey
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="py-12 text-center">
          <div class="flex flex-col gap-4 items-center">
            <FileText class="w-16 h-16 text-base-content/30" />
            <h3 class="text-2xl font-semibold text-base-content/70">Belum Ada Survey Aktif</h3>
            <p class="text-base-content/50">Survey baru akan segera tersedia. Silakan kembali lagi nanti.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16 bg-base-200">
      <div class="container px-4 mx-auto">
        <div class="mb-12 text-center">
          <h2 class="mb-4 text-4xl font-bold">Tentang AKUSUKA</h2>
          <p class="mx-auto max-w-3xl text-xl text-base-content/70">
            Aplikasi kuisioner dan survey kabupaten yang mengutamakan partisipasi mudah dengan manfaat nyata bagi pembangunan daerah.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
           <div class="shadow-lg card bg-base-100">
             <div class="text-center card-body">
               <Zap class="mx-auto mb-4 w-12 h-12 text-primary" />
               <h3 class="justify-center mb-2 card-title">Mudah Digunakan</h3>
               <p class="text-base-content/70">
                 Interface yang intuitif memungkinkan siapa saja dapat membuat survey profesional dalam hitungan menit.
               </p>
             </div>
           </div>
 
           <div class="shadow-lg card bg-base-100">
             <div class="text-center card-body">
               <BarChart3 class="mx-auto mb-4 w-12 h-12 text-secondary" />
               <h3 class="justify-center mb-2 card-title">Analisis Mendalam</h3>
               <p class="text-base-content/70">
                 Dapatkan insight berharga dengan visualisasi data yang komprehensif dan laporan real-time.
               </p>
             </div>
           </div>
 
           <div class="shadow-lg card bg-base-100">
             <div class="text-center card-body">
               <Shield class="mx-auto mb-4 w-12 h-12 text-accent" />
               <h3 class="justify-center mb-2 card-title">Aman & Terpercaya</h3>
               <p class="text-base-content/70">
                 Data responden dilindungi dengan enkripsi tingkat enterprise dan kepatuhan terhadap standar privasi.
               </p>
             </div>
           </div>
         </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="p-10 footer footer-center bg-base-300 text-base-content">
      <aside>
        <div class="flex gap-2 items-center mb-4">
          <FileText class="w-8 h-8 text-primary" />
          <span class="text-2xl font-bold">AKUSUKA</span>
        </div>
        <p class="font-bold">
          Aplikasi kuisioner dan survey kabupaten
        </p>
        <p class="text-sm text-base-content/70">
          Partisipasi Mudah Manfaat Nyata
        </p> 
        <p>Copyright © 2025 - All right reserved</p>
      </aside>
      <nav>
        <div class="grid grid-flow-col gap-4">
          <a href="#about" class="link link-hover">Tentang Kami</a>
          <a href="/entry" class="link link-hover">Ikuti Survey</a>
          <a href="/login" class="link link-hover">Login Admin</a>
          <!-- <a href="/dashboard" class="link link-hover">Dashboard</a> -->
        </div>
      </nav>
    </footer>
  </div>
  
  <!-- Theme Selector Component -->
  <ThemeSelector />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import {
  Menu,
  Users,
  FileText,
  Clock,
  Zap,
  BarChart3,
  Shield,
  LogIn,
  Play
} from 'lucide-vue-next'
import ThemeSelector from '@/Components/ThemeSelector.vue'

// Props from controller
const props = defineProps({
  publicSurveys: {
    type: Array,
    default: () => []
  },
  totalRespondents: {
    type: Number,
    default: 0
  },
  totalResponses: {
    type: Number,
    default: 0
  }
})

const isLoading = ref(false)
const activeSurveys = computed(() => props.publicSurveys)
const totalRespondents = computed(() => props.totalRespondents)
const totalResponses = computed(() => props.totalResponses)
</script>