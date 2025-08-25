<template>
  <CenteredLayout>
    <template #title>Buat Survey Baru</template>
    <template #subtitle>Buat survey untuk mengumpulkan data dan feedback</template>
    
    <template #breadcrumbs>
      <div class="breadcrumbs text-sm">
        <ul>
          <li><a href="/dashboard" class="link link-hover">Dashboard</a></li>
          <li><a href="/surveys" class="link link-hover">Survey</a></li>
          <li>Buat Baru</li>
        </ul>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Survey Basic Info -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title flex items-center gap-2">
            <FileText class="w-5 h-5" />
            Informasi Dasar Survey
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control">
              <label class="label">
                <span class="label-text font-medium">Judul Survey *</span>
              </label>
              <input 
                type="text" 
                placeholder="Masukkan judul survey" 
                class="input input-bordered w-full" 
                v-model="form.title"
              />
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text font-medium">Kategori</span>
              </label>
              <select class="select select-bordered w-full" v-model="form.category">
                <option disabled selected>Pilih kategori</option>
                <option value="customer_satisfaction">Kepuasan Pelanggan</option>
                <option value="employee_feedback">Feedback Karyawan</option>
                <option value="market_research">Riset Pasar</option>
                <option value="product_feedback">Feedback Produk</option>
                <option value="other">Lainnya</option>
              </select>
            </div>
          </div>
          
          <div class="form-control">
            <label class="label">
              <span class="label-text font-medium">Deskripsi</span>
            </label>
            <textarea 
              class="textarea textarea-bordered h-24" 
              placeholder="Jelaskan tujuan dan konteks survey ini"
              v-model="form.description"
            ></textarea>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-control">
              <label class="label">
                <span class="label-text font-medium">Tanggal Mulai</span>
              </label>
              <input 
                type="date" 
                class="input input-bordered" 
                v-model="form.start_date"
              />
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text font-medium">Tanggal Berakhir</span>
              </label>
              <input 
                type="date" 
                class="input input-bordered" 
                v-model="form.end_date"
              />
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text font-medium">Status</span>
              </label>
              <select class="select select-bordered" v-model="form.status">
                <option value="draft">Draft</option>
                <option value="active">Aktif</option>
                <option value="closed">Ditutup</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Survey Sections -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="flex items-center justify-between mb-4">
            <h2 class="card-title flex items-center gap-2">
              <List class="w-5 h-5" />
              Bagian Survey
            </h2>
            <button class="btn btn-primary btn-sm" @click="addSection">
              <Plus class="w-4 h-4" />
              Tambah Bagian
            </button>
          </div>
          
          <div class="space-y-4" v-if="form.sections.length > 0">
            <div 
              v-for="(section, sectionIndex) in form.sections" 
              :key="sectionIndex"
              class="border border-base-300 rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-lg">Bagian {{ sectionIndex + 1 }}</h3>
                <button 
                  class="btn btn-ghost btn-sm text-error"
                  @click="removeSection(sectionIndex)"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-control">
                  <label class="label">
                    <span class="label-text font-medium">Nama Bagian *</span>
                  </label>
                  <input 
                    type="text" 
                    placeholder="Nama bagian" 
                    class="input input-bordered" 
                    v-model="section.name"
                  />
                </div>
                
                <div class="form-control">
                  <label class="label">
                    <span class="label-text font-medium">Urutan</span>
                  </label>
                  <input 
                    type="number" 
                    class="input input-bordered" 
                    v-model="section.order"
                  />
                </div>
              </div>
              
              <div class="form-control mb-4">
                <label class="label">
                  <span class="label-text font-medium">Deskripsi Bagian</span>
                </label>
                <textarea 
                  class="textarea textarea-bordered" 
                  placeholder="Deskripsi bagian ini"
                  v-model="section.description"
                ></textarea>
              </div>
              
              <!-- Questions -->
              <div class="border-t pt-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium">Pertanyaan</h4>
                  <button 
                    class="btn btn-outline btn-sm"
                    @click="addQuestion(sectionIndex)"
                  >
                    <Plus class="w-4 h-4" />
                    Tambah Pertanyaan
                  </button>
                </div>
                
                <div class="space-y-3" v-if="section.questions.length > 0">
                  <div 
                    v-for="(question, questionIndex) in section.questions"
                    :key="questionIndex"
                    class="bg-base-50 p-3 rounded border"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <span class="font-medium text-sm">Pertanyaan {{ questionIndex + 1 }}</span>
                      <button 
                        class="btn btn-ghost btn-xs text-error"
                        @click="removeQuestion(sectionIndex, questionIndex)"
                      >
                        <X class="w-3 h-3" />
                      </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                      <div class="form-control">
                        <input 
                          type="text" 
                          placeholder="Teks pertanyaan" 
                          class="input input-bordered input-sm" 
                          v-model="question.text"
                        />
                      </div>
                      
                      <div class="form-control">
                        <select class="select select-bordered select-sm" v-model="question.type">
                          <option value="text">Teks</option>
                          <option value="textarea">Teks Panjang</option>
                          <option value="radio">Pilihan Tunggal</option>
                          <option value="checkbox">Pilihan Ganda</option>
                          <option value="select">Dropdown</option>
                          <option value="rating">Rating</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                      <label class="label cursor-pointer">
                        <input type="checkbox" class="checkbox checkbox-sm" v-model="question.required" />
                        <span class="label-text ml-2">Wajib diisi</span>
                      </label>
                      
                      <div class="form-control">
                        <input 
                          type="number" 
                          placeholder="Urutan" 
                          class="input input-bordered input-sm w-20" 
                          v-model="question.order"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                
                <div v-else class="text-center py-4 text-base-content/60">
                  <HelpCircle class="w-8 h-8 mx-auto mb-2" />
                  <p>Belum ada pertanyaan. Klik "Tambah Pertanyaan" untuk memulai.</p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-base-content/60">
            <FileText class="w-12 h-12 mx-auto mb-3" />
            <p class="text-lg font-medium mb-2">Belum ada bagian survey</p>
            <p>Klik "Tambah Bagian" untuk membuat bagian pertama survey Anda.</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3">
        <button class="btn btn-ghost" @click="$inertia.visit('/surveys')">
          <X class="w-4 h-4" />
          Batal
        </button>
        <button class="btn btn-outline" @click="saveDraft">
          <Save class="w-4 h-4" />
          Simpan Draft
        </button>
        <button class="btn btn-primary" @click="publishSurvey">
          <Send class="w-4 h-4" />
          Publikasikan
        </button>
      </div>
    </div>
  </CenteredLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { 
  FileText, 
  List, 
  Plus, 
  Trash2, 
  X, 
  Save, 
  Send, 
  HelpCircle 
} from 'lucide-vue-next'

const form = reactive({
  title: '',
  description: '',
  category: '',
  start_date: '',
  end_date: '',
  status: 'draft',
  sections: []
})

const addSection = () => {
  form.sections.push({
    name: '',
    description: '',
    order: form.sections.length + 1,
    questions: []
  })
}

const removeSection = (index) => {
  form.sections.splice(index, 1)
  // Reorder sections
  form.sections.forEach((section, i) => {
    section.order = i + 1
  })
}

const addQuestion = (sectionIndex) => {
  const section = form.sections[sectionIndex]
  section.questions.push({
    text: '',
    type: 'text',
    required: false,
    order: section.questions.length + 1
  })
}

const removeQuestion = (sectionIndex, questionIndex) => {
  const section = form.sections[sectionIndex]
  section.questions.splice(questionIndex, 1)
  // Reorder questions
  section.questions.forEach((question, i) => {
    question.order = i + 1
  })
}

const saveDraft = () => {
  // TODO: Implement save draft functionality
  console.log('Saving draft:', form)
  alert('Draft berhasil disimpan!')
}

const publishSurvey = () => {
  // TODO: Implement publish survey functionality
  if (!form.title) {
    alert('Judul survey harus diisi!')
    return
  }
  
  if (form.sections.length === 0) {
    alert('Survey harus memiliki minimal satu bagian!')
    return
  }
  
  console.log('Publishing survey:', form)
  alert('Survey berhasil dipublikasikan!')
}
</script>