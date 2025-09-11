<template>
  <Head title="Dashboard" />
  
  <DashboardLayout 
  >
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="stat bg-primary text-primary-content rounded-lg">
        <div class="stat-figure">
          <FileText class="w-8 h-8" />
        </div>
        <div class="stat-title text-primary-content/70">Total Survey</div>
        <div class="stat-value">25</div>
        <div class="stat-desc text-primary-content/70">↗︎ 12% dari bulan lalu</div>
      </div>
      
      <div class="stat bg-secondary text-secondary-content rounded-lg">
        <div class="stat-figure">
          <Users class="w-8 h-8" />
        </div>
        <div class="stat-title text-secondary-content/70">Responden</div>
        <div class="stat-value">1,247</div>
        <div class="stat-desc text-secondary-content/70">↗︎ 8% dari bulan lalu</div>
      </div>
      
      <div class="stat bg-accent text-accent-content rounded-lg">
        <div class="stat-figure">
          <CheckCircle class="w-8 h-8" />
        </div>
        <div class="stat-title text-accent-content/70">Selesai</div>
        <div class="stat-value">892</div>
        <div class="stat-desc text-accent-content/70">↗︎ 15% dari bulan lalu</div>
      </div>
      
      <div class="stat bg-info text-info-content rounded-lg">
        <div class="stat-figure">
          <TrendingUp class="w-8 h-8" />
        </div>
        <div class="stat-title text-info-content/70">Tingkat Respon</div>
        <div class="stat-value">71.6%</div>
        <div class="stat-desc text-info-content/70">↗︎ 3% dari bulan lalu</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card bg-base-100 border border-base-300 mb-6">
      <div class="card-body">
        <h2 class="card-title gap-2 mb-4">
          <Zap class="w-5 h-5 text-warning" />
          Aksi Cepat
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Link href="/surveys/create" class="btn btn-primary gap-2">
            <Plus class="w-4 h-4" />
            Buat Survey Baru
          </Link>
          <button class="btn btn-outline btn-secondary gap-2">
            <Send class="w-4 h-4" />
            Kirim Undangan
          </button>
          <Link href="/surveys" class="btn btn-outline btn-accent gap-2">
            <BarChart3 class="w-4 h-4" />
            Lihat Survey
          </Link>
        </div>
      </div>
    </div>

    <!-- Recent Surveys -->
    <div class="card bg-base-100 border border-base-300 mb-6">
      <div class="card-body">
        <div class="flex items-center justify-between mb-4">
          <h2 class="card-title gap-2">
            <Clock class="w-5 h-5 text-info" />
            Survey Terbaru
          </h2>
          <button class="btn btn-ghost btn-sm gap-2">
            Lihat Semua
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>
        
        <div class="overflow-x-auto">
          <table class="table table-zebra">
            <thead>
              <tr>
                <th>Judul Survey</th>
                <th>Status</th>
                <th>Responden</th>
                <th>Dibuat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="survey in recentSurveys" :key="survey.id">
                <td>
                  <div class="flex items-center gap-3">
                    <FileText class="w-4 h-4 text-primary" />
                    <div>
                      <div class="font-bold">
                        <Link :href="`/surveys/${survey.id}`" class="link link-hover">{{ survey.title }}</Link>
                      </div>
                      <div class="text-sm opacity-50">{{ survey.description }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="badge" :class="getStatusBadgeClass(survey.status)">
                    {{ survey.status }}
                  </div>
                </td>
                <td>{{ survey.responses }}/{{ survey.target }}</td>
                <td>{{ formatDate(survey.created_at) }}</td>
                <td>
                  <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                      <MoreVertical class="w-4 h-4" />
                    </div>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                      <li><a class="gap-2"><Eye class="w-4 h-4" />Lihat</a></li>
                      <li><a class="gap-2"><Edit class="w-4 h-4" />Edit</a></li>
                      <li><a class="gap-2"><Share class="w-4 h-4" />Bagikan</a></li>
                      <li><a class="gap-2 text-error"><Trash2 class="w-4 h-4" />Hapus</a></li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
          <h2 class="card-title gap-2 mb-4">
            <PieChart class="w-5 h-5 text-success" />
            Distribusi Status Survey
          </h2>
          <div class="flex items-center justify-center h-48">
            <div class="text-base-content/50 flex flex-col items-center gap-2">
              <BarChart3 class="w-12 h-12" />
              <span>Chart akan ditampilkan di sini</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="card bg-base-100 border border-base-300">
        <div class="card-body">
          <h2 class="card-title gap-2 mb-4">
            <Activity class="w-5 h-5 text-warning" />
            Aktivitas Mingguan
          </h2>
          <div class="flex items-center justify-center h-48">
            <div class="text-base-content/50 flex flex-col items-center gap-2">
              <Activity class="w-12 h-12" />
              <span>Chart akan ditampilkan di sini</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #actions>
      <button class="btn btn-outline gap-2">
        <RefreshCw class="w-4 h-4" />
        Refresh Data
      </button>
      <button class="btn btn-primary gap-2">
        <Settings class="w-4 h-4" />
        Pengaturan
      </button>
    </template>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {
  BarChart3,
  FileText,
  Users,
  CheckCircle,
  TrendingUp,
  Zap,
  Plus,
  Send,
  Download,
  Clock,
  ArrowRight,
  MoreVertical,
  Eye,
  Edit,
  Share,
  Trash2,
  PieChart,
  Activity,
  RefreshCw,
  Settings,
  Home
} from 'lucide-vue-next'

// Breadcrumbs
const breadcrumbs = [
  { title: 'Beranda', url: '/', icon: Home },
  { title: 'Dashboard', icon: BarChart3 }
]

// Sample data
const recentSurveys = ref([
  {
    id: 1,
    title: 'Survey Kepuasan Pelanggan Q4 2024',
    description: 'Evaluasi kepuasan pelanggan triwulan keempat',
    status: 'Aktif',
    responses: 156,
    target: 200,
    created_at: '2024-01-15'
  },
  {
    id: 2,
    title: 'Feedback Produk Baru',
    description: 'Mengumpulkan feedback untuk produk terbaru',
    status: 'Draft',
    responses: 0,
    target: 100,
    created_at: '2024-01-14'
  },
  {
    id: 3,
    title: 'Survey Karyawan 2024',
    description: 'Survey tahunan kepuasan karyawan',
    status: 'Selesai',
    responses: 89,
    target: 89,
    created_at: '2024-01-10'
  }
])

// Helper functions
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Aktif':
      return 'badge-success'
    case 'Draft':
      return 'badge-warning'
    case 'Selesai':
      return 'badge-info'
    default:
      return 'badge-ghost'
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>
