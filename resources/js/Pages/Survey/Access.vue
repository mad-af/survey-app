<template>
  <div class="min-h-screen bg-gradient-to-br from-accent/10 to-info/10 flex items-center justify-center p-4">
    <div class="card w-full max-w-lg bg-base-100 shadow-2xl">
      <div class="card-body">
        <!-- Header -->
        <div class="text-center mb-6">
          <div class="avatar placeholder mb-4">
            <div class="bg-accent text-accent-content rounded-full w-16">
              <Key class="w-8 h-8" />
            </div>
          </div>
          <h1 class="text-2xl font-bold">Akses Survey</h1>
          <p class="text-base-content/60">Masukkan token untuk mengakses survey</p>
        </div>

        <!-- Token Input Form -->
        <div v-if="!surveyFound" class="space-y-6">
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Token Survey</span>
              <span class="label-text-alt">6-8 karakter</span>
            </label>
            <div class="relative">
              <input 
                type="text" 
                placeholder="Contoh: ABC123" 
                class="input input-bordered input-lg w-full text-center text-2xl font-mono tracking-widest uppercase" 
                v-model="token"
                :class="{ 'input-error': error }"
                @input="token = token.toUpperCase()"
                maxlength="8"
                required
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <Scan class="w-5 h-5 text-base-content/40" />
              </div>
            </div>
            <label v-if="error" class="label">
              <span class="label-text-alt text-error">{{ error }}</span>
            </label>
            <label class="label">
              <span class="label-text-alt text-base-content/60">
                Token biasanya diberikan oleh penyelenggara survey
              </span>
            </label>
          </div>

          <div class="form-control">
            <button 
              class="btn btn-primary btn-lg" 
              :class="{ 'loading': isLoading }"
              :disabled="isLoading || token.length < 3"
              @click="checkToken"
            >
              <Search v-if="!isLoading" class="w-5 h-5" />
              Cari Survey
            </button>
          </div>

          <!-- Alternative Methods -->
          <div class="divider">atau</div>
          
          <div class="space-y-3">
            <button class="btn btn-outline btn-block" @click="showQRScanner = true">
              <QrCode class="w-4 h-4" />
              Scan QR Code
            </button>
            
            <a href="/login" class="btn btn-ghost btn-block">
              <User class="w-4 h-4" />
              Login sebagai Admin
            </a>
          </div>

          <!-- Help Section -->
          <div class="alert alert-info">
            <Info class="w-4 h-4" />
            <div class="text-sm">
              <strong>Butuh bantuan?</strong><br>
              Hubungi penyelenggara survey untuk mendapatkan token akses.
            </div>
          </div>
        </div>

        <!-- Survey Found -->
        <div v-else class="space-y-6">
          <div class="text-center">
            <div class="avatar placeholder mb-4">
              <div class="bg-success text-success-content rounded-full w-16">
                <CheckCircle class="w-8 h-8" />
              </div>
            </div>
            <h2 class="text-xl font-bold text-success mb-2">Survey Ditemukan!</h2>
          </div>

          <!-- Survey Info -->
          <div class="card bg-base-50 border border-base-300">
            <div class="card-body">
              <h3 class="card-title text-lg">{{ foundSurvey.title }}</h3>
              <p class="text-base-content/70 mb-4">{{ foundSurvey.description }}</p>
              
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="stat">
                  <div class="stat-title text-xs">Estimasi Waktu</div>
                  <div class="stat-value text-sm">{{ foundSurvey.estimated_time }}</div>
                </div>
                <div class="stat">
                  <div class="stat-title text-xs">Total Pertanyaan</div>
                  <div class="stat-value text-sm">{{ foundSurvey.total_questions }}</div>
                </div>
              </div>
              
              <div class="flex items-center gap-2 mt-4">
                <div class="badge" :class="getStatusClass(foundSurvey.status)">{{ getStatusText(foundSurvey.status) }}</div>
                <div class="badge badge-outline">{{ foundSurvey.category }}</div>
              </div>
            </div>
          </div>

          <!-- Participant Info Form -->
          <div class="space-y-4">
            <h4 class="font-semibold">Informasi Partisipan (Opsional)</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Nama</span>
                </label>
                <input 
                  type="text" 
                  placeholder="Nama lengkap" 
                  class="input input-bordered" 
                  v-model="participant.name"
                />
              </div>
              
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Email</span>
                </label>
                <input 
                  type="email" 
                  placeholder="email@example.com" 
                  class="input input-bordered" 
                  v-model="participant.email"
                />
              </div>
            </div>
            
            <div class="form-control">
              <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" class="checkbox checkbox-primary" v-model="participant.anonymous" />
                <span class="label-text">Partisipasi secara anonim</span>
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button class="btn btn-outline" @click="resetForm">
              <ArrowLeft class="w-4 h-4" />
              Kembali
            </button>
            <button class="btn btn-primary flex-1" @click="startSurvey">
              <Play class="w-4 h-4" />
              Mulai Survey
            </button>
          </div>
        </div>

        <!-- QR Scanner Modal -->
        <div v-if="showQRScanner" class="modal modal-open">
          <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Scan QR Code Survey</h3>
            
            <div class="bg-base-200 rounded-lg p-8 text-center mb-4">
              <QrCode class="w-16 h-16 mx-auto mb-4 text-base-content/40" />
              <p class="text-base-content/60">Arahkan kamera ke QR Code survey</p>
              <p class="text-sm text-base-content/40 mt-2">(Fitur kamera akan diimplementasi)</p>
            </div>
            
            <div class="modal-action">
              <button class="btn btn-outline" @click="showQRScanner = false">Tutup</button>
              <button class="btn btn-primary" @click="simulateQRScan">Simulasi Scan</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
      <p class="text-sm text-base-content/60 text-center">
        © 2024 Survey App. Semua hak dilindungi.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { 
  Key, Search, QrCode, User, Info, CheckCircle, 
  ArrowLeft, Play, Scan
} from 'lucide-vue-next'

const token = ref('')
const error = ref('')
const isLoading = ref(false)
const surveyFound = ref(false)
const showQRScanner = ref(false)

const participant = reactive({
  name: '',
  email: '',
  anonymous: false
})

// Mock survey data
const foundSurvey = ref({
  id: 1,
  title: 'Survey Kepuasan Pelanggan Q4 2024',
  description: 'Bantu kami meningkatkan layanan dengan memberikan feedback tentang pengalaman Anda.',
  estimated_time: '8 menit',
  total_questions: 12,
  status: 'active',
  category: 'Kepuasan Pelanggan'
})

const checkToken = async () => {
  error.value = ''
  
  if (token.value.length < 3) {
    error.value = 'Token minimal 3 karakter'
    return
  }
  
  isLoading.value = true
  
  try {
    // TODO: Implement actual API call to check token
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
    
    // Demo tokens
    const validTokens = ['ABC123', 'XYZ789', 'DEMO01']
    
    if (validTokens.includes(token.value)) {
      surveyFound.value = true
    } else {
      error.value = 'Token tidak valid atau survey sudah berakhir'
    }
  } catch (err) {
    console.error('Token check error:', err)
    error.value = 'Terjadi kesalahan saat memeriksa token'
  } finally {
    isLoading.value = false
  }
}

const simulateQRScan = () => {
  token.value = 'ABC123'
  showQRScanner.value = false
  checkToken()
}

const resetForm = () => {
  surveyFound.value = false
  token.value = ''
  error.value = ''
  participant.name = ''
  participant.email = ''
  participant.anonymous = false
}

const startSurvey = () => {
  // TODO: Save participant info and redirect to survey
  const surveySlug = `survey-${foundSurvey.value.id}-${token.value.toLowerCase()}`
  window.location.href = `/survey/${surveySlug}`
}

const getStatusClass = (status) => {
  const classes = {
    'active': 'badge-success',
    'draft': 'badge-warning',
    'closed': 'badge-error'
  }
  return classes[status] || 'badge-neutral'
}

const getStatusText = (status) => {
  const texts = {
    'active': 'Aktif',
    'draft': 'Draft',
    'closed': 'Ditutup'
  }
  return texts[status] || status
}
</script>