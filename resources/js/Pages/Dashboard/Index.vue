<template>
  <Head title="Dashboard" />
  
  <DashboardLayout pageTitle="Dashboard">
    <!-- Welcome Section -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-base-content">Dashboard</h1>
          <p class="mt-1 text-base-content/70">Selamat datang kembali! Berikut adalah ringkasan aktivitas survey Anda.</p>
        </div>
        <div class="flex gap-3">
          <button class="gap-2 btn btn-outline" @click="refreshData">
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
      <!-- Total Surveys -->
      <div class="shadow-lg card bg-primary text-primary-content">
        <div class="card-body">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium opacity-80">Total Survey</p>
              <p class="text-2xl font-bold">{{ props.statistics.totalSurveys }}</p>
              <p class="mt-1 text-xs opacity-70">{{ props.statistics.activeSurveys }} aktif</p>
            </div>
            <div class="p-3 rounded-lg bg-white/20">
              <FileText class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Total Responses -->
      <div class="shadow-lg card bg-success text-success-content">
        <div class="card-body">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium opacity-80">Total Respons</p>
              <p class="text-2xl font-bold">{{ props.statistics.totalResponses }}</p>
              <p class="mt-1 text-xs opacity-70">{{ props.statistics.completedResponses }} selesai</p>
            </div>
            <div class="p-3 rounded-lg bg-white/20">
              <CheckCircle class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Total Respondents -->
      <div class="shadow-lg card bg-info text-info-content">
        <div class="card-body">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium opacity-80">Total Responden</p>
              <p class="text-2xl font-bold">{{ props.statistics.totalRespondents }}</p>
              <p class="mt-1 text-xs opacity-70">{{ props.statistics.newRespondentsThisMonth }} baru bulan ini</p>
            </div>
            <div class="p-3 rounded-lg bg-white/20">
              <Users class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>

      <!-- Completion Rate -->
      <div class="shadow-lg card bg-warning text-warning-content">
        <div class="card-body">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium opacity-80">Tingkat Penyelesaian</p>
              <p class="text-2xl font-bold">{{ props.statistics.completionRate }}%</p>
              <p class="mt-1 text-xs opacity-70">Rata-rata semua survey</p>
            </div>
            <div class="p-3 rounded-lg bg-white/20">
              <TrendingUp class="w-6 h-6" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Recent Surveys -->
      <div class="lg:col-span-2">
        <div class="border card bg-base-100 border-base-300">
          <div class="card-body">
            <div class="flex justify-between items-center mb-4">
              <h2 class="gap-2 card-title">
                <FileText class="w-5 h-5 text-primary" />
                Survey Terbaru
              </h2>
              <Link href="/dashboard/survey" class="gap-2 btn btn-sm btn-outline">
                <Eye class="w-4 h-4" />
                Lihat Semua
              </Link>
            </div>
            
            <div class="space-y-3">
              <div v-for="survey in props.recentSurveys" :key="survey.id" 
                   class="flex justify-between items-center p-4 rounded-lg border transition-colors bg-base-50 border-base-200 hover:border-primary/30">
                <div class="flex-1">
                  <h3 class="font-semibold text-base-content">{{ survey.title }}</h3>
                  <p class="mt-1 text-sm text-base-content/60">{{ survey.description }}</p>
                  <div class="flex gap-3 items-center mt-2">
                    <span class="badge" :class="getStatusBadgeClass(survey.status)">{{ getStatusLabel(survey.status) }}</span>
                    <span class="text-xs text-base-content/50">{{ survey.responses_count }} respons</span>
                    <span class="text-xs text-base-content/50">{{ formatDate(survey.created_at) }}</span>
                  </div>
                </div>
                <div class="flex gap-2">
                  <Link :href="`/dashboard/survey/${survey.id}`" class="gap-1 btn btn-sm btn-ghost">
                    <Eye class="w-3 h-3" />
                    Lihat
                  </Link>
                  <Link :href="`/dashboard/survey/${survey.id}/responses`" class="gap-1 btn btn-sm btn-primary">
                    <BarChart3 class="w-3 h-3" />
                    Hasil
                  </Link>
                </div>
              </div>
              
              <div v-if="props.recentSurveys.length === 0" class="py-8 text-center">
                <FileText class="mx-auto mb-3 w-12 h-12 text-base-content/30" />
                <p class="text-base-content/60">Belum ada survey yang dibuat</p>
                <Link href="/dashboard/survey" class="gap-2 mt-3 btn btn-sm btn-primary">
                  <Plus class="w-4 h-4" />
                  Buat Survey Pertama
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Sidebar -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="border card bg-base-100 border-base-300">
          <div class="card-body">
            <h2 class="gap-2 mb-4 card-title">
              <Zap class="w-5 h-5 text-warning" />
              Aksi Cepat
            </h2>
            <div class="space-y-3">
              <Link href="/dashboard/survey" class="gap-2 w-full btn btn-sm btn-primary">
                <Plus class="w-4 h-4" />
                Buat Survey Baru
              </Link>
              <Link href="/dashboard/survey" class="gap-2 w-full btn btn-sm btn-outline">
                <FileText class="w-4 h-4" />
                Kelola Survey
              </Link>
              <Link href="/dashboard/user-management" class="gap-2 w-full btn btn-sm btn-outline">
                <Users class="w-4 h-4" />
                Kelola Pengguna
              </Link>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <!-- <div class="border card bg-base-100 border-base-300">
          <div class="card-body">
            <h2 class="gap-2 mb-4 card-title">
              <Activity class="w-5 h-5 text-info" />
              Aktivitas Terbaru
            </h2>
            <div class="space-y-3">
              <div v-for="activity in props.recentActivities" :key="activity.id" 
                   class="flex gap-3 items-start p-3 rounded-lg bg-base-50">
                <div class="p-2 rounded-full" :class="getActivityIconClass(activity.type)">
                  <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-base-content">{{ activity.title }}</p>
                  <p class="mt-1 text-xs text-base-content/60">{{ activity.description }}</p>
                  <p class="mt-1 text-xs text-base-content/50">{{ formatTimeAgo(activity.created_at) }}</p>
                </div>
              </div>
              
              <div v-if="props.recentActivities.length === 0" class="py-4 text-center">
                <Activity class="mx-auto mb-2 w-8 h-8 text-base-content/30" />
                <p class="text-sm text-base-content/60">Belum ada aktivitas</p>
              </div>
            </div>
          </div>
        </div> -->

        <!-- Survey Status Distribution -->
        <div class="border card bg-base-100 border-base-300">
          <div class="card-body">
            <h2 class="gap-2 mb-4 card-title">
              <PieChart class="w-5 h-5 text-success" />
              Status Survey
            </h2>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <div class="flex gap-2 items-center">
                  <div class="w-3 h-3 rounded-full bg-success"></div>
                  <span class="text-sm">Aktif</span>
                </div>
                <span class="text-sm font-medium">{{ props.statistics.activeSurveys }}</span>
              </div>
              <div class="flex justify-between items-center">
                <div class="flex gap-2 items-center">
                  <div class="w-3 h-3 rounded-full bg-warning"></div>
                  <span class="text-sm">Draft</span>
                </div>
                <span class="text-sm font-medium">{{ props.statistics.draftSurveys }}</span>
              </div>
              <div class="flex justify-between items-center">
                <div class="flex gap-2 items-center">
                  <div class="w-3 h-3 rounded-full bg-error"></div>
                  <span class="text-sm">Ditutup</span>
                </div>
                <span class="text-sm font-medium">{{ props.statistics.closedSurveys }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { 
  FileText,
  Users,
  CheckCircle,
  TrendingUp,
  Plus,
  Eye,
  BarChart3,
  Zap,
  Activity,
  PieChart,
  RefreshCw,
  UserPlus,
  MessageSquare,
  Settings
} from 'lucide-vue-next'

// Props from controller
const props = defineProps({
  statistics: {
    type: Object,
    required: true
  },
  recentSurveys: {
    type: Array,
    required: true
  },
  recentActivities: {
    type: Array,
    required: true
  }
})

// Reactive data
const isRefreshing = ref(false)

// Helper functions
const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'badge-warning',
    active: 'badge-success',
    closed: 'badge-error'
  }
  return `badge ${classes[status] || 'badge-ghost'}`
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    active: 'Aktif',
    closed: 'Ditutup'
  }
  return labels[status] || status
}

const getActivityIcon = (type) => {
  const icons = {
    survey_created: FileText,
    response_received: MessageSquare,
    user_registered: UserPlus,
    survey_completed: CheckCircle
  }
  return icons[type] || Activity
}

const getActivityIconClass = (type) => {
  const classes = {
    survey_created: 'bg-primary/20 text-primary',
    response_received: 'bg-success/20 text-success',
    user_registered: 'bg-info/20 text-info',
    survey_completed: 'bg-warning/20 text-warning'
  }
  return classes[type] || 'bg-base-200 text-base-content'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = Math.floor((now - date) / (1000 * 60 * 60))
  
  if (diffInHours < 1) return 'Baru saja'
  if (diffInHours < 24) return `${diffInHours} jam yang lalu`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays} hari yang lalu`
  
  return formatDate(dateString)
}

const refreshData = async () => {
  isRefreshing.value = true
  // Simulate API call
  setTimeout(() => {
    isRefreshing.value = false
  }, 1000)
}

// Load data on component mount
onMounted(() => {
  // In real application, fetch data from API here
})
</script>