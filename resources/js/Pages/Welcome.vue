<template>
  <Head title="Selamat Datang" />
  
  <CenteredLayout 
    title="Selamat Datang di Survey App!"
    subtitle="Aplikasi survey modern dengan teknologi terdepan"
    :title-icon="Rocket"
    app-name="Survey App"
  >
    <!-- Welcome Message -->
    <div class="alert alert-success mb-6">
      <Sparkles class="w-6 h-6" />
      <div>
        <h3 class="font-bold">Konfigurasi Berhasil!</h3>
        <div class="text-sm">🎉 Semua teknologi telah dikonfigurasi dengan sempurna!</div>
      </div>
    </div>
    
    <!-- Tech Stack -->
    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6">
      <div class="card-body">
        <h2 class="card-title gap-2 mb-4">
          <Code class="w-5 h-5 text-primary" />
          Teknologi yang Digunakan
        </h2>
        <p class="text-base-content/70 mb-4">
          Aplikasi ini dibangun dengan teknologi modern dan terpercaya untuk memberikan pengalaman terbaik.
        </p>
                
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">L</span>
            </div>
            <div>
              <div class="font-semibold text-sm">Laravel 12</div>
              <div class="text-xs text-base-content/60">Backend Framework</div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
            <Zap class="w-8 h-8 text-blue-500" />
            <div>
              <div class="font-semibold text-sm">Inertia.js</div>
              <div class="text-xs text-base-content/60">SPA Bridge</div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">V</span>
            </div>
            <div>
              <div class="font-semibold text-sm">Vue.js 3</div>
              <div class="text-xs text-base-content/60">Frontend Framework</div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
            <Paintbrush class="w-8 h-8 text-purple-500" />
            <div>
              <div class="font-semibold text-sm">DaisyUI</div>
              <div class="text-xs text-base-content/60">UI Components</div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
            <Star class="w-8 h-8 text-yellow-500" />
            <div>
              <div class="font-semibold text-sm">Lucide Icons</div>
              <div class="text-xs text-base-content/60">Icon Library</div>
            </div>
          </div>
          
          <div class="flex items-center gap-2 p-3 bg-base-100 rounded-lg border border-base-300">
            <Palette class="w-8 h-8 text-cyan-500" />
            <div>
              <div class="font-semibold text-sm">Tailwind CSS</div>
              <div class="text-xs text-base-content/60">Styling</div>
            </div>
          </div>
        </div>
      </div>
    </div>
                
    <!-- Quick Actions -->
    <div class="card bg-base-100 border border-base-300 mb-6">
      <div class="card-body">
        <h2 class="card-title gap-2 mb-4">
          <Zap class="w-5 h-5 text-warning" />
          Mulai Sekarang
        </h2>
        <p class="text-base-content/70 mb-4">
          Jelajahi fitur-fitur yang tersedia atau mulai membuat survey pertama Anda.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <a href="/dashboard" class="btn btn-primary gap-2">
            <BarChart3 class="w-4 h-4" />
            Dashboard
          </a>
          <a href="/surveys" class="btn btn-outline btn-secondary gap-2">
            <FileText class="w-4 h-4" />
            Lihat Survey
          </a>
          <a href="/test-daisy" class="btn btn-outline btn-accent gap-2">
            <TestTube class="w-4 h-4" />
            Test Components
          </a>
        </div>
      </div>
    </div>
    
    <!-- Interactive Demo -->
    <div class="card bg-base-100 border border-base-300">
      <div class="card-body">
        <h2 class="card-title gap-2 mb-4">
          <Play class="w-5 h-5 text-info" />
          Demo Interaktif
        </h2>
        <p class="text-base-content/70 mb-4">
          Coba fitur-fitur interaktif untuk melihat bagaimana aplikasi bekerja.
        </p>
        
        <div class="flex flex-wrap gap-4 mb-6">
          <button 
            @click="showMessage = !showMessage"
            class="btn btn-outline gap-2"
          >
            <component :is="showMessage ? EyeOff : Eye" class="w-4 h-4" />
            {{ showMessage ? 'Sembunyikan' : 'Tampilkan' }} Pesan
          </button>
          
          <button 
            @click="toggleAnimation = !toggleAnimation"
            class="btn btn-outline gap-2"
            :class="{ 'btn-active': toggleAnimation }"
          >
            <Sparkles class="w-4 h-4" :class="{ 'animate-spin': toggleAnimation }" />
            {{ toggleAnimation ? 'Stop' : 'Start' }} Animasi
          </button>
        </div>
        
        <div v-if="showMessage" class="alert alert-info mb-4">
          <Info class="w-6 h-6" />
          <div>
            <h3 class="font-bold">Pesan Demo</h3>
            <div class="text-sm">Ini adalah contoh pesan interaktif menggunakan DaisyUI!</div>
          </div>
        </div>
        
        <div class="stats stats-vertical lg:stats-horizontal shadow w-full">
          <div class="stat">
            <div class="stat-figure text-primary">
              <Users class="w-8 h-8" />
            </div>
            <div class="stat-title">Total Pengguna</div>
            <div class="stat-value text-primary">1,247</div>
            <div class="stat-desc">↗︎ 12% dari bulan lalu</div>
          </div>
          
          <div class="stat">
            <div class="stat-figure text-secondary">
              <FileText class="w-8 h-8" />
            </div>
            <div class="stat-title">Survey Aktif</div>
            <div class="stat-value text-secondary">25</div>
            <div class="stat-desc">↗︎ 8% dari minggu lalu</div>
          </div>
          
          <div class="stat">
            <div class="stat-figure text-accent">
              <CheckCircle class="w-8 h-8" />
            </div>
            <div class="stat-title">Response Rate</div>
            <div class="stat-value text-accent">89%</div>
            <div class="stat-desc">↗︎ 5% dari bulan lalu</div>
          </div>
        </div>
      </div>
    </div>
    
    <template #actions>
      <button class="btn btn-outline gap-2">
        <BookOpen class="w-4 h-4" />
        Dokumentasi
      </button>
      <button class="btn btn-primary gap-2">
        <Rocket class="w-4 h-4" />
        Mulai Sekarang
      </button>
    </template>
  </CenteredLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { 
  Sparkles, 
  Rocket, 
  CheckCircle, 
  Zap, 
  Palette, 
  Paintbrush, 
  Star, 
  Eye, 
  EyeOff,
  Code,
  BarChart3,
  FileText,
  TestTube,
  Play,
  Info,
  Users,
  BookOpen
} from 'lucide-vue-next'

const showMessage = ref(false)
const toggleAnimation = ref(false)
</script>