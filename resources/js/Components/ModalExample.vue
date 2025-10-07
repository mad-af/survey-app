<template>
  <div class="p-8 space-y-4">
    <h2 class="text-2xl font-bold mb-6">Contoh Penggunaan Confirmation Modal</h2>
    
    <!-- Example buttons to trigger different modals -->
    <div class="flex flex-wrap gap-4">
      <!-- Basic confirmation -->
      <button 
        class="btn btn-primary" 
        @click="openBasicModal"
      >
        Hapus Data
      </button>
      
      <!-- Custom confirmation -->
      <button 
        class="btn btn-warning" 
        @click="openCustomModal"
      >
        Logout
      </button>
      
      <!-- Success confirmation -->
      <button 
        class="btn btn-success" 
        @click="openSuccessModal"
      >
        Simpan Perubahan
      </button>
      
      <!-- Danger confirmation -->
      <button 
        class="btn btn-error" 
        @click="openDangerModal"
      >
        Reset Semua Data
      </button>
    </div>
    
    <!-- Display last action -->
    <div v-if="lastAction" class="alert alert-info mt-4">
      <span>Aksi terakhir: {{ lastAction }}</span>
    </div>
    
    <!-- Basic Modal -->
    <ConfirmationModal
      ref="basicModal"
      modal-id="basic_modal"
      title="Hapus Data"
      message="Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan."
      confirm-text="Hapus"
      cancel-text="Batal"
      confirm-button-type="error"
      @confirm="handleBasicConfirm"
      @cancel="handleCancel"
    />
    
    <!-- Custom Modal -->
    <ConfirmationModal
      ref="customModal"
      modal-id="custom_modal"
      title="Logout"
      message="Anda akan keluar dari sistem. Pastikan semua pekerjaan sudah disimpan."
      confirm-text="Ya, Logout"
      cancel-text="Batal"
      confirm-button-type="warning"
      cancel-button-type="outline"
      @confirm="handleCustomConfirm"
      @cancel="handleCancel"
    />
    
    <!-- Success Modal -->
    <ConfirmationModal
      ref="successModal"
      modal-id="success_modal"
      title="Simpan Perubahan"
      message="Apakah Anda yakin ingin menyimpan semua perubahan?"
      confirm-text="Simpan"
      cancel-text="Batal"
      confirm-button-type="success"
      :loading="isLoading"
      @confirm="handleSuccessConfirm"
      @cancel="handleCancel"
    />
    
    <!-- Danger Modal with custom content -->
    <ConfirmationModal
      ref="dangerModal"
      modal-id="danger_modal"
      title="Reset Semua Data"
      message="Tindakan ini akan menghapus SEMUA data yang ada. Pastikan Anda sudah membuat backup."
      confirm-text="Ya, Reset Semua"
      cancel-text="Batal"
      confirm-button-type="error"
      @confirm="handleDangerConfirm"
      @cancel="handleCancel"
    >
      <template #content>
        <div class="bg-error/10 border border-error/20 rounded-lg p-4 mt-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-error" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span class="font-semibold text-error">Peringatan!</span>
          </div>
          <p class="text-sm mt-2 text-error/80">
            Data yang akan dihapus meliputi:
          </p>
          <ul class="text-sm mt-1 text-error/80 list-disc list-inside">
            <li>Semua survey dan respons</li>
            <li>Data pengguna</li>
            <li>Pengaturan sistem</li>
          </ul>
        </div>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ConfirmationModal from './ConfirmationModal.vue'

// Refs for modal components
const basicModal = ref(null)
const customModal = ref(null)
const successModal = ref(null)
const dangerModal = ref(null)

// State
const lastAction = ref('')
const isLoading = ref(false)

// Methods to open modals
const openBasicModal = () => {
  basicModal.value?.openModal()
}

const openCustomModal = () => {
  customModal.value?.openModal()
}

const openSuccessModal = () => {
  successModal.value?.openModal()
}

const openDangerModal = () => {
  dangerModal.value?.openModal()
}

// Event handlers
const handleBasicConfirm = () => {
  lastAction.value = 'Data berhasil dihapus'
  console.log('Basic modal confirmed')
}

const handleCustomConfirm = () => {
  lastAction.value = 'User berhasil logout'
  console.log('Custom modal confirmed')
}

const handleSuccessConfirm = async () => {
  isLoading.value = true
  
  // Simulate API call
  setTimeout(() => {
    isLoading.value = false
    lastAction.value = 'Perubahan berhasil disimpan'
    console.log('Success modal confirmed')
  }, 2000)
}

const handleDangerConfirm = () => {
  lastAction.value = 'Semua data berhasil direset'
  console.log('Danger modal confirmed')
}

const handleCancel = () => {
  lastAction.value = 'Aksi dibatalkan'
  console.log('Modal cancelled')
}
</script>