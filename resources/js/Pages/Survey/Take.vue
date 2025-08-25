<template>
  <div class="min-h-screen bg-base-200">
    <!-- Header -->
    <div class="bg-base-100 shadow-sm">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="avatar placeholder">
              <div class="bg-primary text-primary-content rounded-full w-10">
                <span class="text-lg">S</span>
              </div>
            </div>
            <div>
              <h1 class="text-xl font-bold">Survey App</h1>
              <p class="text-sm text-base-content/60">Survei Online</p>
            </div>
          </div>
          
          <div class="flex items-center gap-4">
            <!-- Progress -->
            <div class="hidden md:flex items-center gap-2">
              <span class="text-sm font-medium">Progress:</span>
              <progress class="progress progress-primary w-32" :value="progress" max="100"></progress>
              <span class="text-sm font-medium">{{ Math.round(progress) }}%</span>
            </div>
            
            <!-- Language Switcher -->
            <div class="dropdown dropdown-end">
              <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
                <Globe class="w-4 h-4" />
                ID
              </div>
              <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-32 p-2 shadow">
                <li><a>🇮🇩 Indonesia</a></li>
                <li><a>🇺🇸 English</a></li>
              </ul>
            </div>
          </div>
        </div>
        
        <!-- Mobile Progress -->
        <div class="md:hidden mt-3">
          <div class="flex items-center justify-between text-sm mb-1">
            <span>Progress</span>
            <span>{{ Math.round(progress) }}%</span>
          </div>
          <progress class="progress progress-primary w-full" :value="progress" max="100"></progress>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <!-- Survey Introduction -->
      <div v-if="currentStep === 'intro'" class="card bg-base-100 shadow-xl">
        <div class="card-body text-center">
          <div class="mb-6">
            <div class="avatar placeholder mb-4">
              <div class="bg-primary text-primary-content rounded-full w-20">
                <FileText class="w-10 h-10" />
              </div>
            </div>
            <h1 class="text-3xl font-bold mb-2">{{ survey.title }}</h1>
            <p class="text-base-content/70 text-lg">{{ survey.description }}</p>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="stat bg-base-50 rounded-lg">
              <div class="stat-figure text-primary">
                <Clock class="w-6 h-6" />
              </div>
              <div class="stat-title text-sm">Estimasi Waktu</div>
              <div class="stat-value text-lg">{{ survey.estimated_time }}</div>
            </div>
            
            <div class="stat bg-base-50 rounded-lg">
              <div class="stat-figure text-secondary">
                <List class="w-6 h-6" />
              </div>
              <div class="stat-title text-sm">Total Pertanyaan</div>
              <div class="stat-value text-lg">{{ survey.total_questions }}</div>
            </div>
            
            <div class="stat bg-base-50 rounded-lg">
              <div class="stat-figure text-accent">
                <Shield class="w-6 h-6" />
              </div>
              <div class="stat-title text-sm">Privasi</div>
              <div class="stat-value text-lg">Aman</div>
            </div>
          </div>
          
          <div class="text-left bg-base-50 p-4 rounded-lg mb-6">
            <h3 class="font-semibold mb-2 flex items-center gap-2">
              <Info class="w-4 h-4" />
              Informasi Penting:
            </h3>
            <ul class="text-sm space-y-1 text-base-content/80">
              <li>• Jawaban Anda akan dijaga kerahasiaannya</li>
              <li>• Anda dapat menyimpan progress dan melanjutkan nanti</li>
              <li>• Tidak ada jawaban yang benar atau salah</li>
              <li>• Mohon jawab dengan jujur sesuai pengalaman Anda</li>
            </ul>
          </div>
          
          <div class="flex gap-3 justify-center">
            <button class="btn btn-outline" @click="$inertia.visit('/')">
              <ArrowLeft class="w-4 h-4" />
              Kembali
            </button>
            <button class="btn btn-primary btn-lg" @click="startSurvey">
              Mulai Survey
              <ArrowRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Survey Questions -->
      <div v-else-if="currentStep === 'survey'" class="space-y-6">
        <!-- Section Header -->
        <div class="card bg-base-100 shadow-sm">
          <div class="card-body py-4">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl font-bold">{{ currentSection.name }}</h2>
                <p class="text-base-content/60">{{ currentSection.description }}</p>
              </div>
              <div class="text-right">
                <div class="text-sm text-base-content/60">Bagian {{ currentSectionIndex + 1 }} dari {{ survey.sections.length }}</div>
                <div class="text-sm font-medium">Pertanyaan {{ currentQuestionIndex + 1 }} dari {{ currentSection.questions.length }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Question Card -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <div class="mb-6">
              <div class="flex items-start gap-3 mb-4">
                <span class="badge badge-primary badge-lg">{{ currentQuestionIndex + 1 }}</span>
                <div class="flex-1">
                  <h3 class="text-lg font-semibold mb-2">{{ currentQuestion.text }}</h3>
                  <div class="flex items-center gap-2">
                    <span v-if="currentQuestion.required" class="badge badge-error badge-sm">Wajib diisi</span>
                    <span class="badge badge-outline badge-sm">{{ getQuestionTypeText(currentQuestion.type) }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Question Input -->
            <div class="mb-6">
              <!-- Text Input -->
              <div v-if="currentQuestion.type === 'text'">
                <input 
                  type="text" 
                  class="input input-bordered w-full" 
                  :placeholder="getPlaceholder(currentQuestion.type)"
                  v-model="currentAnswer"
                />
              </div>
              
              <!-- Textarea -->
              <div v-else-if="currentQuestion.type === 'textarea'">
                <textarea 
                  class="textarea textarea-bordered w-full h-32" 
                  :placeholder="getPlaceholder(currentQuestion.type)"
                  v-model="currentAnswer"
                ></textarea>
              </div>
              
              <!-- Radio Buttons -->
              <div v-else-if="currentQuestion.type === 'radio'" class="space-y-3">
                <label 
                  v-for="option in currentQuestion.options" 
                  :key="option.id"
                  class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-50"
                >
                  <input 
                    type="radio" 
                    :name="`question-${currentQuestion.id}`" 
                    :value="option.value"
                    class="radio radio-primary" 
                    v-model="currentAnswer"
                  />
                  <span>{{ option.text }}</span>
                </label>
              </div>
              
              <!-- Checkboxes -->
              <div v-else-if="currentQuestion.type === 'checkbox'" class="space-y-3">
                <label 
                  v-for="option in currentQuestion.options" 
                  :key="option.id"
                  class="flex items-center gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-50"
                >
                  <input 
                    type="checkbox" 
                    :value="option.value"
                    class="checkbox checkbox-primary" 
                    v-model="currentAnswer"
                  />
                  <span>{{ option.text }}</span>
                </label>
              </div>
              
              <!-- Select Dropdown -->
              <div v-else-if="currentQuestion.type === 'select'">
                <select class="select select-bordered w-full" v-model="currentAnswer">
                  <option disabled selected>Pilih salah satu...</option>
                  <option 
                    v-for="option in currentQuestion.options" 
                    :key="option.id"
                    :value="option.value"
                  >
                    {{ option.text }}
                  </option>
                </select>
              </div>
              
              <!-- Rating -->
              <div v-else-if="currentQuestion.type === 'rating'" class="text-center">
                <div class="rating rating-lg gap-2 mb-4">
                  <input 
                    v-for="n in 5" 
                    :key="n"
                    type="radio" 
                    :name="`rating-${currentQuestion.id}`" 
                    class="mask mask-star-2 bg-orange-400" 
                    :value="n"
                    v-model="currentAnswer"
                  />
                </div>
                <div class="flex justify-between text-sm text-base-content/60">
                  <span>Sangat Tidak Puas</span>
                  <span>Sangat Puas</span>
                </div>
              </div>
            </div>
            
            <!-- Navigation -->
            <div class="flex justify-between items-center">
              <button 
                class="btn btn-outline" 
                @click="previousQuestion"
                :disabled="currentQuestionIndex === 0 && currentSectionIndex === 0"
              >
                <ArrowLeft class="w-4 h-4" />
                Sebelumnya
              </button>
              
              <div class="flex gap-2">
                <button class="btn btn-ghost" @click="saveProgress">
                  <Save class="w-4 h-4" />
                  Simpan Progress
                </button>
                
                <button 
                  class="btn btn-primary" 
                  @click="nextQuestion"
                  :disabled="currentQuestion.required && !currentAnswer"
                >
                  {{ isLastQuestion ? 'Selesai' : 'Selanjutnya' }}
                  <ArrowRight class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Survey Completion -->
      <div v-else-if="currentStep === 'completed'" class="card bg-base-100 shadow-xl">
        <div class="card-body text-center">
          <div class="mb-6">
            <div class="avatar placeholder mb-4">
              <div class="bg-success text-success-content rounded-full w-20">
                <CheckCircle class="w-10 h-10" />
              </div>
            </div>
            <h1 class="text-3xl font-bold mb-2">Terima Kasih!</h1>
            <p class="text-base-content/70 text-lg">Survey telah berhasil diselesaikan</p>
          </div>
          
          <div class="bg-base-50 p-6 rounded-lg mb-6">
            <h3 class="font-semibold mb-4">Ringkasan Partisipasi Anda:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="stat">
                <div class="stat-title">Waktu Pengerjaan</div>
                <div class="stat-value text-lg">{{ completionTime }}</div>
              </div>
              <div class="stat">
                <div class="stat-title">Pertanyaan Dijawab</div>
                <div class="stat-value text-lg">{{ survey.total_questions }}</div>
              </div>
              <div class="stat">
                <div class="stat-title">Tingkat Kelengkapan</div>
                <div class="stat-value text-lg">100%</div>
              </div>
            </div>
          </div>
          
          <div class="text-left bg-info/10 p-4 rounded-lg mb-6">
            <h3 class="font-semibold mb-2 flex items-center gap-2">
              <Info class="w-4 h-4" />
              Apa yang terjadi selanjutnya?
            </h3>
            <ul class="text-sm space-y-1 text-base-content/80">
              <li>• Jawaban Anda akan dianalisis bersama responden lainnya</li>
              <li>• Hasil survey akan digunakan untuk perbaikan layanan</li>
              <li>• Identitas Anda tetap terjaga kerahasiaannya</li>
              <li>• Anda mungkin akan dihubungi untuk survey lanjutan (opsional)</li>
            </ul>
          </div>
          
          <div class="flex gap-3 justify-center">
            <button class="btn btn-outline" @click="$inertia.visit('/')">
              <Home class="w-4 h-4" />
              Kembali ke Beranda
            </button>
            <button class="btn btn-primary" @click="shareExperience">
              <Share class="w-4 h-4" />
              Bagikan Pengalaman
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-base-100 border-t mt-12">
      <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="text-sm text-base-content/60">
            © 2024 Survey App. Semua hak dilindungi.
          </div>
          <div class="flex items-center gap-4 text-sm">
            <a href="#" class="link link-hover">Kebijakan Privasi</a>
            <a href="#" class="link link-hover">Syarat & Ketentuan</a>
            <a href="#" class="link link-hover">Bantuan</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  FileText, Clock, List, Shield, Info, ArrowLeft, ArrowRight,
  Globe, Save, CheckCircle, Home, Share
} from 'lucide-vue-next'

// Mock survey data
const survey = ref({
  id: 1,
  title: 'Survey Kepuasan Pelanggan Q4 2024',
  description: 'Bantu kami meningkatkan layanan dengan memberikan feedback tentang pengalaman Anda.',
  estimated_time: '8 menit',
  total_questions: 12,
  sections: [
    {
      id: 1,
      name: 'Informasi Demografis',
      description: 'Pertanyaan dasar tentang profil Anda',
      questions: [
        {
          id: 1,
          text: 'Berapa usia Anda?',
          type: 'select',
          required: true,
          options: [
            { id: 1, value: '18-25', text: '18-25 tahun' },
            { id: 2, value: '26-35', text: '26-35 tahun' },
            { id: 3, value: '36-45', text: '36-45 tahun' },
            { id: 4, value: '46-55', text: '46-55 tahun' },
            { id: 5, value: '55+', text: 'Di atas 55 tahun' }
          ]
        },
        {
          id: 2,
          text: 'Apa jenis kelamin Anda?',
          type: 'radio',
          required: true,
          options: [
            { id: 1, value: 'male', text: 'Laki-laki' },
            { id: 2, value: 'female', text: 'Perempuan' },
            { id: 3, value: 'other', text: 'Lainnya' }
          ]
        }
      ]
    },
    {
      id: 2,
      name: 'Pengalaman Produk',
      description: 'Pertanyaan tentang pengalaman Anda dengan produk kami',
      questions: [
        {
          id: 3,
          text: 'Seberapa puas Anda dengan produk kami secara keseluruhan?',
          type: 'rating',
          required: true
        },
        {
          id: 4,
          text: 'Apa yang paling Anda sukai dari produk kami?',
          type: 'textarea',
          required: false
        },
        {
          id: 5,
          text: 'Fitur mana yang paling sering Anda gunakan? (Pilih semua yang sesuai)',
          type: 'checkbox',
          required: false,
          options: [
            { id: 1, value: 'dashboard', text: 'Dashboard' },
            { id: 2, value: 'reports', text: 'Laporan' },
            { id: 3, value: 'analytics', text: 'Analitik' },
            { id: 4, value: 'integrations', text: 'Integrasi' }
          ]
        }
      ]
    }
  ]
})

const currentStep = ref('intro') // 'intro', 'survey', 'completed'
const currentSectionIndex = ref(0)
const currentQuestionIndex = ref(0)
const responses = ref({})
const startTime = ref(null)
const completionTime = ref('')

const currentSection = computed(() => survey.value.sections[currentSectionIndex.value])
const currentQuestion = computed(() => currentSection.value.questions[currentQuestionIndex.value])
const currentAnswer = ref('')

const progress = computed(() => {
  if (currentStep.value === 'intro') return 0
  if (currentStep.value === 'completed') return 100
  
  const totalQuestions = survey.value.sections.reduce((sum, section) => sum + section.questions.length, 0)
  const answeredQuestions = Object.keys(responses.value).length
  return (answeredQuestions / totalQuestions) * 100
})

const isLastQuestion = computed(() => {
  const isLastSection = currentSectionIndex.value === survey.value.sections.length - 1
  const isLastQuestionInSection = currentQuestionIndex.value === currentSection.value.questions.length - 1
  return isLastSection && isLastQuestionInSection
})

const startSurvey = () => {
  currentStep.value = 'survey'
  startTime.value = new Date()
  loadCurrentAnswer()
}

const loadCurrentAnswer = () => {
  const questionId = currentQuestion.value.id
  currentAnswer.value = responses.value[questionId] || (currentQuestion.value.type === 'checkbox' ? [] : '')
}

const saveCurrentAnswer = () => {
  const questionId = currentQuestion.value.id
  responses.value[questionId] = currentAnswer.value
}

const nextQuestion = () => {
  saveCurrentAnswer()
  
  if (isLastQuestion.value) {
    completeSurvey()
    return
  }
  
  if (currentQuestionIndex.value < currentSection.value.questions.length - 1) {
    currentQuestionIndex.value++
  } else {
    currentSectionIndex.value++
    currentQuestionIndex.value = 0
  }
  
  loadCurrentAnswer()
}

const previousQuestion = () => {
  saveCurrentAnswer()
  
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  } else if (currentSectionIndex.value > 0) {
    currentSectionIndex.value--
    currentQuestionIndex.value = survey.value.sections[currentSectionIndex.value].questions.length - 1
  }
  
  loadCurrentAnswer()
}

const saveProgress = () => {
  saveCurrentAnswer()
  // TODO: Save to backend
  alert('Progress berhasil disimpan! Anda dapat melanjutkan nanti.')
}

const completeSurvey = () => {
  const endTime = new Date()
  const duration = Math.round((endTime - startTime.value) / 1000 / 60) // minutes
  completionTime.value = `${duration} menit`
  
  currentStep.value = 'completed'
  
  // TODO: Submit responses to backend
  console.log('Survey completed:', responses.value)
}

const shareExperience = () => {
  // TODO: Implement sharing functionality
  alert('Fitur berbagi akan segera tersedia!')
}

const getQuestionTypeText = (type) => {
  const types = {
    'text': 'Teks Singkat',
    'textarea': 'Teks Panjang',
    'radio': 'Pilihan Tunggal',
    'checkbox': 'Pilihan Ganda',
    'select': 'Dropdown',
    'rating': 'Rating Bintang'
  }
  return types[type] || type
}

const getPlaceholder = (type) => {
  const placeholders = {
    'text': 'Ketik jawaban Anda di sini...',
    'textarea': 'Tuliskan jawaban Anda dengan detail...'
  }
  return placeholders[type] || ''
}

onMounted(() => {
  // Load saved progress if exists
  // TODO: Check for saved progress from backend
})
</script>