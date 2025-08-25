<template>
  <Head title="Daftar Survey" />
  
  <CenteredLayout 
    title="Daftar Survey"
    subtitle="Kelola semua survey Anda dalam satu tempat"
    :title-icon="FileText"
    :breadcrumbs="breadcrumbs"
  >
    <!-- Filter dan Search -->
    <div class="card bg-base-100 border border-base-300 mb-6">
      <div class="card-body">
        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
          <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            <!-- Search -->
            <div class="form-control">
              <div class="input-group">
                <input 
                  type="text" 
                  placeholder="Cari survey..." 
                  class="input input-bordered w-full max-w-xs"
                  v-model="searchQuery"
                />
                <button class="btn btn-square btn-primary">
                  <Search class="w-4 h-4" />
                </button>
              </div>
            </div>
            
            <!-- Filter Status -->
            <select class="select select-bordered w-full max-w-xs" v-model="selectedStatus">
              <option value="">Semua Status</option>
              <option value="draft">Draft</option>
              <option value="active">Aktif</option>
              <option value="completed">Selesai</option>
              <option value="archived">Arsip</option>
            </select>
          </div>
          
          <button class="btn btn-primary gap-2">
            <Plus class="w-4 h-4" />
            Buat Survey Baru
          </button>
        </div>
      </div>
    </div>

    <!-- Survey Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
      <div 
        v-for="survey in filteredSurveys" 
        :key="survey.id"
        class="card bg-base-100 border border-base-300 hover:shadow-lg transition-shadow duration-200"
      >
        <div class="card-body">
          <!-- Survey Header -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2">
              <FileText class="w-5 h-5 text-primary" />
              <div class="badge" :class="getStatusBadgeClass(survey.status)">
                {{ getStatusText(survey.status) }}
              </div>
            </div>
            <div class="dropdown dropdown-end">
              <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                <MoreVertical class="w-4 h-4" />
              </div>
              <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-48">
                <li><a class="gap-2"><Eye class="w-4 h-4" />Lihat Detail</a></li>
                <li><a class="gap-2"><Edit class="w-4 h-4" />Edit</a></li>
                <li><a class="gap-2"><Copy class="w-4 h-4" />Duplikat</a></li>
                <li><a class="gap-2"><Share class="w-4 h-4" />Bagikan</a></li>
                <li class="divider"></li>
                <li><a class="gap-2 text-error"><Trash2 class="w-4 h-4" />Hapus</a></li>
              </ul>
            </div>
          </div>
          
          <!-- Survey Title & Description -->
          <h3 class="card-title text-lg mb-2">
            <a :href="`/surveys/${survey.id}`" class="link link-hover">{{ survey.title }}</a>
          </h3>
          <p class="text-base-content/70 text-sm mb-4 line-clamp-2">{{ survey.description }}</p>
          
          <!-- Survey Stats -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="stat p-0">
              <div class="stat-title text-xs">Responden</div>
              <div class="stat-value text-lg">{{ survey.responses }}</div>
              <div class="stat-desc text-xs">dari {{ survey.target }} target</div>
            </div>
            <div class="stat p-0">
              <div class="stat-title text-xs">Progress</div>
              <div class="stat-value text-lg">{{ Math.round((survey.responses / survey.target) * 100) }}%</div>
              <div class="stat-desc text-xs">{{ survey.questions }} pertanyaan</div>
            </div>
          </div>
          
          <!-- Progress Bar -->
          <div class="w-full">
            <div class="flex justify-between text-xs mb-1">
              <span>Progress</span>
              <span>{{ survey.responses }}/{{ survey.target }}</span>
            </div>
            <progress 
              class="progress progress-primary w-full" 
              :value="survey.responses" 
              :max="survey.target"
            ></progress>
          </div>
          
          <!-- Survey Meta -->
          <div class="flex items-center justify-between text-xs text-base-content/60 mt-4 pt-4 border-t border-base-300">
            <div class="flex items-center gap-1">
              <Calendar class="w-3 h-3" />
              <span>{{ formatDate(survey.created_at) }}</span>
            </div>
            <div class="flex items-center gap-1">
              <User class="w-3 h-3" />
              <span>{{ survey.author }}</span>
            </div>
          </div>
          
          <!-- Card Actions -->
          <div class="card-actions justify-end mt-4">
            <button class="btn btn-sm btn-outline gap-1">
              <BarChart3 class="w-3 h-3" />
              Analisis
            </button>
            <button class="btn btn-sm btn-primary gap-1">
              <Play class="w-3 h-3" />
              {{ survey.status === 'draft' ? 'Publikasikan' : 'Lihat' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredSurveys.length === 0" class="card bg-base-100 border border-base-300">
      <div class="card-body text-center py-12">
        <div class="flex flex-col items-center gap-4">
          <div class="w-24 h-24 rounded-full bg-base-200 flex items-center justify-center">
            <FileText class="w-12 h-12 text-base-content/30" />
          </div>
          <div>
            <h3 class="text-xl font-bold mb-2">Belum ada survey</h3>
            <p class="text-base-content/70 mb-4">
              {{ searchQuery || selectedStatus ? 'Tidak ada survey yang sesuai dengan filter' : 'Mulai dengan membuat survey pertama Anda' }}
            </p>
            <button v-if="!searchQuery && !selectedStatus" class="btn btn-primary gap-2">
              <Plus class="w-4 h-4" />
              Buat Survey Pertama
            </button>
            <button v-else class="btn btn-outline gap-2" @click="clearFilters">
              <X class="w-4 h-4" />
              Hapus Filter
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="filteredSurveys.length > 0" class="flex justify-center mt-8">
      <div class="join">
        <button class="join-item btn btn-outline">
          <ChevronLeft class="w-4 h-4" />
        </button>
        <button class="join-item btn btn-outline btn-active">1</button>
        <button class="join-item btn btn-outline">2</button>
        <button class="join-item btn btn-outline">3</button>
        <button class="join-item btn btn-outline">
          <ChevronRight class="w-4 h-4" />
        </button>
      </div>
    </div>

    <template #actions>
      <div class="flex gap-2">
        <button class="btn btn-outline btn-sm">
          <Filter class="w-4 h-4" />
          Filter
        </button>
        <a href="/surveys/create" class="btn btn-primary btn-sm">
          <Plus class="w-4 h-4" />
          Buat Survey
        </a>
      </div>
    </template>
  </CenteredLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import {
  FileText,
  Search,
  Plus,
  MoreVertical,
  Eye,
  Edit,
  Copy,
  Share,
  Trash2,
  Calendar,
  User,
  BarChart3,
  Play,
  X,
  ChevronLeft,
  ChevronRight,
  Download,
  Filter,
  Home
} from 'lucide-vue-next'

// Breadcrumbs
const breadcrumbs = [
  { title: 'Beranda', url: '/', icon: Home },
  { title: 'Survey', icon: FileText }
]

// Reactive data
const searchQuery = ref('')
const selectedStatus = ref('')

// Sample surveys data
const surveys = ref([
  {
    id: 1,
    title: 'Survey Kepuasan Pelanggan Q4 2024',
    description: 'Evaluasi komprehensif kepuasan pelanggan untuk triwulan keempat tahun 2024, mencakup berbagai aspek layanan dan produk.',
    status: 'active',
    responses: 156,
    target: 200,
    questions: 15,
    created_at: '2024-01-15',
    author: 'Admin'
  },
  {
    id: 2,
    title: 'Feedback Produk Mobile App',
    description: 'Mengumpulkan feedback pengguna untuk aplikasi mobile terbaru yang baru saja diluncurkan.',
    status: 'draft',
    responses: 0,
    target: 100,
    questions: 12,
    created_at: '2024-01-14',
    author: 'Product Manager'
  },
  {
    id: 3,
    title: 'Survey Karyawan Tahunan 2024',
    description: 'Survey tahunan untuk mengukur kepuasan dan engagement karyawan di seluruh departemen.',
    status: 'completed',
    responses: 89,
    target: 89,
    questions: 25,
    created_at: '2024-01-10',
    author: 'HR Manager'
  },
  {
    id: 4,
    title: 'Riset Pasar Produk Baru',
    description: 'Penelitian pasar untuk memahami kebutuhan dan preferensi konsumen terhadap produk baru.',
    status: 'active',
    responses: 67,
    target: 150,
    questions: 18,
    created_at: '2024-01-08',
    author: 'Marketing Team'
  },
  {
    id: 5,
    title: 'Evaluasi Training Program',
    description: 'Evaluasi efektivitas program training yang telah diselenggarakan bulan lalu.',
    status: 'archived',
    responses: 45,
    target: 45,
    questions: 10,
    created_at: '2024-01-05',
    author: 'Training Coordinator'
  }
])

// Computed properties
const filteredSurveys = computed(() => {
  let filtered = surveys.value
  
  if (searchQuery.value) {
    filtered = filtered.filter(survey => 
      survey.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      survey.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  
  if (selectedStatus.value) {
    filtered = filtered.filter(survey => survey.status === selectedStatus.value)
  }
  
  return filtered
})

// Helper functions
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'active':
      return 'badge-success'
    case 'draft':
      return 'badge-warning'
    case 'completed':
      return 'badge-info'
    case 'archived':
      return 'badge-neutral'
    default:
      return 'badge-ghost'
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 'active':
      return 'Aktif'
    case 'draft':
      return 'Draft'
    case 'completed':
      return 'Selesai'
    case 'archived':
      return 'Arsip'
    default:
      return status
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>