<template>

  <Head title="Pendaftaran Respondent" />

  <CenteredLayout title="📝 Pendaftaran Respondent" subtitle="Silakan lengkapi data diri Anda untuk mengikuti survey"
    max-width="max-w-2xl">

    <!-- Geolocation Status -->
    <div v-if="geolocation.loading" class="mb-4 alert alert-info">
      <Info class="w-5 h-5" />
      <span>Sedang mengambil lokasi Anda...</span>
    </div>

    <div v-else-if="geolocation.error" class="mb-4 alert alert-warning">
      <Info class="w-5 h-5" />
      <div>
        <div class="font-bold">Informasi Lokasi</div>
        <div class="text-sm">{{ geolocation.error }}</div>
      </div>
    </div>

    <div v-else-if="geolocation.latitude && geolocation.longitude" class="mb-4 alert alert-success">
      <Info class="w-5 h-5" />
      <div>
        <div class="font-bold">Lokasi Berhasil Diambil</div>
        <div class="text-sm">Koordinat: {{ geolocation.latitude.toFixed(6) }}, {{ geolocation.longitude.toFixed(6) }}
        </div>
      </div>
    </div>
    <!-- Registration Form -->
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Personal Information Section -->
      <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="fieldset-legend">Informasi Pribadi</legend>

        <!-- External ID -->
        <label class="label">
          ID Eksternal
          <div class="tooltip tooltip-right" data-tip="ID dari sistem lain (NIM/NIP/nomor karyawan) untuk menghubungkan data survei dengan sistem eksternal">
            <HelpCircle class="w-4 h-4 opacity-70" />
          </div>
        </label>
        <input v-model="form.external_id" type="text" placeholder="ID atau kode unik (opsional)" class="input"
          :class="{ 'input-error': form.errors.external_id }" />
        <div v-if="form.errors.external_id" class="mt-1 text-sm text-error">{{ form.errors.external_id }}</div>

        <!-- Name -->
        <label class="label">Nama Lengkap <span class="text-error">*</span></label>
        <input v-model="form.name" type="text" placeholder="Masukkan nama lengkap Anda" class="input"
          :class="{ 'input-error': form.errors.name }" required />
        <div v-if="form.errors.name" class="mt-1 text-sm text-error">{{ form.errors.name }}</div>

        <!-- Email -->
        <label class="label">Email</label>
        <input v-model="form.email" type="email" placeholder="alamat@email.com" class="input"
          :class="{ 'input-error': form.errors.email }" />
        <div v-if="form.errors.email" class="mt-1 text-sm text-error">{{ form.errors.email }}</div>

        <!-- Phone -->
        <label class="label">Nomor Telepon</label>
        <input v-model="form.phone" type="tel" placeholder="08xxxxxxxxxx" class="input"
          :class="{ 'input-error': form.errors.phone }" />
        <div v-if="form.errors.phone" class="mt-1 text-sm text-error">{{ form.errors.phone }}</div>

        <!-- Gender -->
        <label class="label">Jenis Kelamin <span class="text-error">*</span></label>
        <select v-model="form.gender" class="select" :class="{ 'select-error': form.errors.gender }" required>
          <option value="">Pilih jenis kelamin</option>
          <option value="male">Laki-laki</option>
          <option value="female">Perempuan</option>
        </select>
        <div v-if="form.errors.gender" class="mt-1 text-sm text-error">{{ form.errors.gender }}</div>

        <!-- Birth Year -->
        <label class="label">Tahun Lahir <span class="text-error">*</span></label>
        <input v-model.number="form.birth_year" type="number" placeholder="1990" min="1900"
          :max="new Date().getFullYear()" class="input" :class="{ 'input-error': form.errors.birth_year }" required />
        <div v-if="form.errors.birth_year" class="mt-1 text-sm text-error">{{ form.errors.birth_year }}</div>
      </fieldset>

      <!-- Professional Information Section -->
      <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="fieldset-legend">Informasi Profesional</legend>

        <!-- Organization -->
        <label class="label">Organisasi/Perusahaan</label>
        <input v-model="form.organization" type="text" placeholder="Nama organisasi atau perusahaan" class="input"
          :class="{ 'input-error': form.errors.organization }" />
        <div v-if="form.errors.organization" class="mt-1 text-sm text-error">{{ form.errors.organization }}</div>

        <!-- Department -->
        <label class="label">Departemen/Divisi</label>
        <input v-model="form.department" type="text" placeholder="Nama departemen atau divisi" class="input"
          :class="{ 'input-error': form.errors.department }" />
        <div v-if="form.errors.department" class="mt-1 text-sm text-error">{{ form.errors.department }}</div>

        <!-- Role Title -->
        <label class="label">Jabatan/Posisi</label>
        <input v-model="form.role_title" type="text" placeholder="Jabatan atau posisi Anda" class="input"
          :class="{ 'input-error': form.errors.role_title }" />
        <div v-if="form.errors.role_title" class="mt-1 text-sm text-error">{{ form.errors.role_title }}</div>

        <!-- Location -->
        <label class="label">Lokasi</label>
        <input v-model="form.location" type="text" placeholder="Kota, Provinsi" class="input"
          :class="{ 'input-error': form.errors.location }" />
        <div v-if="form.errors.location" class="mt-1 text-sm text-error">{{ form.errors.location }}</div>
      </fieldset>

      <!-- Consent Section -->
      <div class="form-control">
        <label class="cursor-pointer label whitespace-break-spaces">
          <span class="label-text">
            <input v-model="form.consent" type="checkbox" class="mr-3 checkbox checkbox-primary" />
            <span>
              Saya menyetujui penggunaan data pribadi untuk keperluan survey ini<span class="text-error">*</span>
            </span>
          </span>
        </label>
        <label v-if="form.errors.consent" class="label">
          <span class="label-text-alt text-error">{{ form.errors.consent }}</span>
        </label>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full btn btn-primary" :class="{ 'loading': form.processing }"
        :disabled="form.processing || !form.name || !form.gender || !form.birth_year || !form.consent">
        <LogIn v-if="!form.processing" class="mr-2 w-5 h-5" />
        {{ form.processing ? 'Mengirim...' : (props.existingRespondent ? 'Update Data' : 'Submit Formulir') }}
      </button>
    </form>

    <!-- Info Section -->
    <div class="mt-6 alert alert-info">
      <Info class="w-5 h-5" />
      <div>
        <h3 class="font-bold">Informasi Privasi</h3>
        <div class="mt-1 text-sm">
          Data yang Anda berikan akan digunakan untuk keperluan survey dan akan dijaga kerahasiaannya sesuai dengan
          kebijakan privasi kami.
        </div>
      </div>
    </div>

  </CenteredLayout>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { LogIn, Info, HelpCircle } from 'lucide-vue-next'

// Define props
const props = defineProps({
  survey: {
    type: Object,
    required: true
  },
  surveyCode: {
    type: String,
    required: true
  },
  existingRespondent: {
    type: Object,
    default: null
  }
})

// Geolocation state
const geolocation = ref({
  latitude: null,
  longitude: null,
  accuracy: null,
  error: null,
  loading: false
})

// Form setup with all Respondent model fields
const form = useForm({
  external_id: props.existingRespondent?.external_id || '',
  name: props.existingRespondent?.name || '',
  email: props.existingRespondent?.email || '',
  phone: props.existingRespondent?.phone || '',
  gender: props.existingRespondent?.gender || '',
  birth_year: props.existingRespondent?.birth_year || null,
  organization: props.existingRespondent?.organization || '',
  department: props.existingRespondent?.department || '',
  role_title: props.existingRespondent?.role_title || '',
  location: props.existingRespondent?.location || '',
  demographics: props.existingRespondent?.demographics || {},
  consent: props.existingRespondent?.consent || false,
  consent_at: props.existingRespondent?.consent_at || null
})

// Get user geolocation
const getUserLocation = () => {
  if (!navigator.geolocation) {
    geolocation.value.error = 'Geolocation tidak didukung oleh browser ini'
    return
  }

  geolocation.value.loading = true
  geolocation.value.error = null

  navigator.geolocation.getCurrentPosition(
    (position) => {
      geolocation.value.latitude = position.coords.latitude
      geolocation.value.longitude = position.coords.longitude
      geolocation.value.accuracy = position.coords.accuracy
      geolocation.value.loading = false

      // Update demographics with geolocation data
      form.demographics = {
        ...form.demographics,
        geolocation: {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy,
          timestamp: new Date().toISOString()
        }
      }

      console.log('Geolocation obtained:', {
        latitude: position.coords.latitude,
        longitude: position.coords.longitude,
        accuracy: position.coords.accuracy
      })
    },
    (error) => {
      geolocation.value.loading = false
      let errorMessage = 'Gagal mendapatkan lokasi'

      switch (error.code) {
        case error.PERMISSION_DENIED:
          errorMessage = 'Akses lokasi ditolak oleh user'
          break
        case error.POSITION_UNAVAILABLE:
          errorMessage = 'Informasi lokasi tidak tersedia'
          break
        case error.TIMEOUT:
          errorMessage = 'Permintaan lokasi timeout'
          break
      }

      geolocation.value.error = errorMessage
      console.error('Geolocation error:', errorMessage)
    },
    {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 300000 // 5 minutes
    }
  )
}

// Get location when component mounts
onMounted(() => {
  getUserLocation()
})

// Submit form function
const submitForm = () => {
  // Set consent timestamp when form is submitted
  if (form.consent) {
    form.consent_at = new Date().toISOString()
  }

  // Submit to the registerRespondent route
  form.post(`/survey/${props.surveyCode}/register`, {
    onSuccess: () => {
      // Success will be handled by Inertia.location() in controller
      console.log('Registration successful')
    },
    onError: (errors) => {
      // Handle validation errors
      console.error('Registration failed:', errors)
    }
  })
}
</script>