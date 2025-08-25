<template>
  <CenteredLayout>
    <template #title>{{ survey.title }}</template>
    <template #subtitle>Detail dan statistik survey</template>
    
    <template #breadcrumbs>
      <div class="breadcrumbs text-sm">
        <ul>
          <li><a href="/dashboard" class="link link-hover">Dashboard</a></li>
          <li><a href="/surveys" class="link link-hover">Survey</a></li>
          <li>{{ survey.title }}</li>
        </ul>
      </div>
    </template>

    <template #actions>
      <div class="flex gap-2">
        <button class="btn btn-outline btn-sm">
          <Edit class="w-4 h-4" />
          Edit
        </button>
        <button class="btn btn-primary btn-sm">
          <Share class="w-4 h-4" />
          Bagikan
        </button>
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
            <MoreVertical class="w-4 h-4" />
          </div>
          <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
            <li><a><Copy class="w-4 h-4" />Duplikat Survey</a></li>
            <li><a><Download class="w-4 h-4" />Export Data</a></li>
            <li><a><Archive class="w-4 h-4" />Arsipkan</a></li>
            <li class="divider"></li>
            <li><a class="text-error"><Trash2 class="w-4 h-4" />Hapus Survey</a></li>
          </ul>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Survey Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="stat bg-base-100 shadow rounded-lg">
          <div class="stat-figure text-primary">
            <Users class="w-8 h-8" />
          </div>
          <div class="stat-title">Total Responden</div>
          <div class="stat-value text-primary">{{ survey.total_responses }}</div>
          <div class="stat-desc">{{ survey.response_rate }}% response rate</div>
        </div>
        
        <div class="stat bg-base-100 shadow rounded-lg">
          <div class="stat-figure text-secondary">
            <Calendar class="w-8 h-8" />
          </div>
          <div class="stat-title">Periode</div>
          <div class="stat-value text-secondary text-lg">{{ formatDateRange(survey.start_date, survey.end_date) }}</div>
          <div class="stat-desc">{{ getDaysRemaining(survey.end_date) }}</div>
        </div>
        
        <div class="stat bg-base-100 shadow rounded-lg">
          <div class="stat-figure text-accent">
            <Clock class="w-8 h-8" />
          </div>
          <div class="stat-title">Rata-rata Waktu</div>
          <div class="stat-value text-accent">{{ survey.avg_completion_time }}</div>
          <div class="stat-desc">untuk menyelesaikan</div>
        </div>
        
        <div class="stat bg-base-100 shadow rounded-lg">
          <div class="stat-figure">
            <div class="badge badge-lg" :class="getStatusClass(survey.status)">{{ getStatusText(survey.status) }}</div>
          </div>
          <div class="stat-title">Status</div>
          <div class="stat-value text-sm">{{ survey.category }}</div>
          <div class="stat-desc">Kategori survey</div>
        </div>
      </div>

      <!-- Survey Info -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title flex items-center gap-2">
            <Info class="w-5 h-5" />
            Informasi Survey
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="font-semibold mb-2">Deskripsi</h3>
              <p class="text-base-content/80">{{ survey.description }}</p>
              
              <h3 class="font-semibold mt-4 mb-2">Detail</h3>
              <div class="space-y-1 text-sm">
                <p><span class="font-medium">Dibuat:</span> {{ formatDate(survey.created_at) }}</p>
                <p><span class="font-medium">Terakhir diupdate:</span> {{ formatDate(survey.updated_at) }}</p>
                <p><span class="font-medium">Pembuat:</span> {{ survey.creator.name }}</p>
              </div>
            </div>
            
            <div>
              <h3 class="font-semibold mb-2">Link Survey</h3>
              <div class="join w-full">
                <input 
                  type="text" 
                  :value="survey.public_url" 
                  readonly 
                  class="input input-bordered join-item flex-1" 
                />
                <button class="btn btn-outline join-item" @click="copyLink">
                  <Copy class="w-4 h-4" />
                </button>
              </div>
              
              <h3 class="font-semibold mt-4 mb-2">QR Code</h3>
              <div class="bg-white p-4 rounded-lg inline-block">
                <div class="w-32 h-32 bg-base-300 rounded flex items-center justify-center">
                  <QrCode class="w-16 h-16" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Survey Sections -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title flex items-center gap-2">
            <List class="w-5 h-5" />
            Struktur Survey ({{ survey.sections.length }} Bagian)
          </h2>
          
          <div class="space-y-4">
            <div 
              v-for="(section, index) in survey.sections" 
              :key="section.id"
              class="collapse collapse-arrow bg-base-50 border border-base-300"
            >
              <input type="checkbox" :id="`section-${index}`" /> 
              <label :for="`section-${index}`" class="collapse-title text-lg font-medium">
                {{ section.name }} ({{ section.questions.length }} pertanyaan)
              </label>
              <div class="collapse-content">
                <p class="text-base-content/70 mb-4">{{ section.description }}</p>
                
                <div class="space-y-2">
                  <div 
                    v-for="(question, qIndex) in section.questions"
                    :key="question.id"
                    class="flex items-start gap-3 p-3 bg-base-100 rounded border"
                  >
                    <span class="badge badge-outline">{{ qIndex + 1 }}</span>
                    <div class="flex-1">
                      <p class="font-medium">{{ question.text }}</p>
                      <div class="flex items-center gap-2 mt-1">
                        <span class="badge badge-sm">{{ getQuestionTypeText(question.type) }}</span>
                        <span v-if="question.required" class="badge badge-error badge-sm">Wajib</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Response Analytics -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
              <BarChart3 class="w-5 h-5" />
              Statistik Respons
            </h2>
            
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span>Respons Selesai</span>
                <div class="flex items-center gap-2">
                  <progress class="progress progress-primary w-20" :value="survey.completion_rate" max="100"></progress>
                  <span class="text-sm font-medium">{{ survey.completion_rate }}%</span>
                </div>
              </div>
              
              <div class="flex justify-between items-center">
                <span>Respons Parsial</span>
                <div class="flex items-center gap-2">
                  <progress class="progress progress-warning w-20" :value="survey.partial_rate" max="100"></progress>
                  <span class="text-sm font-medium">{{ survey.partial_rate }}%</span>
                </div>
              </div>
              
              <div class="flex justify-between items-center">
                <span>Tingkat Drop-off</span>
                <div class="flex items-center gap-2">
                  <progress class="progress progress-error w-20" :value="survey.dropout_rate" max="100"></progress>
                  <span class="text-sm font-medium">{{ survey.dropout_rate }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
              <TrendingUp class="w-5 h-5" />
              Respons Harian
            </h2>
            
            <div class="h-48 flex items-center justify-center bg-base-50 rounded">
              <div class="text-center text-base-content/60">
                <BarChart3 class="w-12 h-12 mx-auto mb-2" />
                <p>Grafik respons harian</p>
                <p class="text-sm">(Chart akan diimplementasi)</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Responses -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="flex items-center justify-between mb-4">
            <h2 class="card-title flex items-center gap-2">
              <MessageSquare class="w-5 h-5" />
              Respons Terbaru
            </h2>
            <a href="/surveys/1/responses" class="btn btn-outline btn-sm">
              Lihat Semua
              <ExternalLink class="w-4 h-4" />
            </a>
          </div>
          
          <div class="overflow-x-auto">
            <table class="table table-zebra">
              <thead>
                <tr>
                  <th>Responden</th>
                  <th>Status</th>
                  <th>Progress</th>
                  <th>Waktu Mulai</th>
                  <th>Waktu Selesai</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="response in survey.recent_responses" :key="response.id">
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                          <span class="text-xs">{{ response.respondent.initials }}</span>
                        </div>
                      </div>
                      <div>
                        <div class="font-bold">{{ response.respondent.name || 'Anonim' }}</div>
                        <div class="text-sm opacity-50">{{ response.respondent.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="getResponseStatusClass(response.status)">
                      {{ getResponseStatusText(response.status) }}
                    </span>
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <progress class="progress progress-primary w-16" :value="response.progress" max="100"></progress>
                      <span class="text-sm">{{ response.progress }}%</span>
                    </div>
                  </td>
                  <td>{{ formatDateTime(response.started_at) }}</td>
                  <td>{{ response.completed_at ? formatDateTime(response.completed_at) : '-' }}</td>
                  <td>
                    <button class="btn btn-ghost btn-xs">
                      <Eye class="w-3 h-3" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </CenteredLayout>
</template>

<script setup>
import { ref } from 'vue'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { 
  Edit, Share, MoreVertical, Copy, Download, Archive, Trash2,
  Users, Calendar, Clock, Info, List, BarChart3, TrendingUp,
  MessageSquare, ExternalLink, Eye, QrCode
} from 'lucide-vue-next'

// Mock data - in real app this would come from props or API
const survey = ref({
  id: 1,
  title: 'Survey Kepuasan Pelanggan Q4 2024',
  description: 'Survey untuk mengukur tingkat kepuasan pelanggan terhadap layanan dan produk kami selama kuartal keempat tahun 2024.',
  category: 'Kepuasan Pelanggan',
  status: 'active',
  start_date: '2024-10-01',
  end_date: '2024-12-31',
  created_at: '2024-09-15T10:00:00Z',
  updated_at: '2024-10-15T14:30:00Z',
  creator: {
    name: 'Admin User'
  },
  public_url: 'https://survey-app.com/s/customer-satisfaction-q4-2024',
  total_responses: 1247,
  response_rate: 78.5,
  completion_rate: 85,
  partial_rate: 12,
  dropout_rate: 3,
  avg_completion_time: '8 menit',
  sections: [
    {
      id: 1,
      name: 'Informasi Demografis',
      description: 'Pertanyaan dasar tentang profil responden',
      questions: [
        { id: 1, text: 'Berapa usia Anda?', type: 'select', required: true },
        { id: 2, text: 'Apa jenis kelamin Anda?', type: 'radio', required: true },
        { id: 3, text: 'Di kota mana Anda tinggal?', type: 'text', required: false }
      ]
    },
    {
      id: 2,
      name: 'Pengalaman Produk',
      description: 'Pertanyaan tentang pengalaman menggunakan produk kami',
      questions: [
        { id: 4, text: 'Seberapa puas Anda dengan produk kami?', type: 'rating', required: true },
        { id: 5, text: 'Apa yang paling Anda sukai dari produk kami?', type: 'textarea', required: false },
        { id: 6, text: 'Fitur mana yang paling sering Anda gunakan?', type: 'checkbox', required: false }
      ]
    }
  ],
  recent_responses: [
    {
      id: 1,
      respondent: { name: 'John Doe', email: 'john@example.com', initials: 'JD' },
      status: 'completed',
      progress: 100,
      started_at: '2024-10-15T09:30:00Z',
      completed_at: '2024-10-15T09:38:00Z'
    },
    {
      id: 2,
      respondent: { name: 'Jane Smith', email: 'jane@example.com', initials: 'JS' },
      status: 'in_progress',
      progress: 65,
      started_at: '2024-10-15T10:15:00Z',
      completed_at: null
    },
    {
      id: 3,
      respondent: { name: null, email: null, initials: 'AN' },
      status: 'completed',
      progress: 100,
      started_at: '2024-10-15T11:00:00Z',
      completed_at: '2024-10-15T11:07:00Z'
    }
  ]
})

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDateRange = (startDate, endDate) => {
  const start = new Date(startDate).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })
  const end = new Date(endDate).toLocaleDateString('id-ID', { month: 'short', day: 'numeric' })
  return `${start} - ${end}`
}

const getDaysRemaining = (endDate) => {
  const today = new Date()
  const end = new Date(endDate)
  const diffTime = end - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays > 0) {
    return `${diffDays} hari tersisa`
  } else if (diffDays === 0) {
    return 'Berakhir hari ini'
  } else {
    return 'Sudah berakhir'
  }
}

const getStatusClass = (status) => {
  const classes = {
    'active': 'badge-success',
    'draft': 'badge-warning',
    'closed': 'badge-error',
    'archived': 'badge-neutral'
  }
  return classes[status] || 'badge-neutral'
}

const getStatusText = (status) => {
  const texts = {
    'active': 'Aktif',
    'draft': 'Draft',
    'closed': 'Ditutup',
    'archived': 'Diarsipkan'
  }
  return texts[status] || status
}

const getQuestionTypeText = (type) => {
  const types = {
    'text': 'Teks',
    'textarea': 'Teks Panjang',
    'radio': 'Pilihan Tunggal',
    'checkbox': 'Pilihan Ganda',
    'select': 'Dropdown',
    'rating': 'Rating'
  }
  return types[type] || type
}

const getResponseStatusClass = (status) => {
  const classes = {
    'completed': 'badge-success',
    'in_progress': 'badge-warning',
    'abandoned': 'badge-error'
  }
  return classes[status] || 'badge-neutral'
}

const getResponseStatusText = (status) => {
  const texts = {
    'completed': 'Selesai',
    'in_progress': 'Sedang Berjalan',
    'abandoned': 'Ditinggalkan'
  }
  return texts[status] || status
}

const copyLink = () => {
  navigator.clipboard.writeText(survey.value.public_url)
  alert('Link berhasil disalin!')
}
</script>