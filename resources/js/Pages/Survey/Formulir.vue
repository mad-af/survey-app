<template>

  <Head title="Pendaftaran Respondent" />

  <CenteredLayout title="📝 Pendaftaran Respondent" subtitle="Silakan lengkapi data diri Anda untuk mengikuti survey"
    max-width="max-w-2xl">
    <!-- Registration Form -->
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Personal Information Section -->
      <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="fieldset-legend">Informasi Pribadi</legend>

        <!-- External ID -->
        <label class="label">ID Eksternal</label>
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
        <label class="label">Tahun Lahir</label>
        <input v-model.number="form.birth_year" type="number" placeholder="1990" min="1900"
          :max="new Date().getFullYear()" class="input" :class="{ 'input-error': form.errors.birth_year }" />
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
            <input v-model="form.consent" type="checkbox" class="mr-3 checkbox checkbox-primary" required />
            <span>
              Saya menyetujui penggunaan data pribadi untuk keperluan survey ini
            </span>
            <span class="ml-1 text-error">*</span>
          </span>
        </label>
        <label v-if="form.errors.consent" class="label">
          <span class="label-text-alt text-error">{{ form.errors.consent }}</span>
        </label>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full btn btn-primary" :class="{ 'loading': form.processing }"
        :disabled="form.processing || !form.name || !form.consent">
        <LogIn v-if="!form.processing" class="mr-2 w-5 h-5" />
        {{ form.processing ? 'Mengirim...' : 'Submit Formulir' }}
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
import CenteredLayout from '@/Layouts/CenteredLayout.vue'
import { LogIn, Info } from 'lucide-vue-next'

// Form setup with all Respondent model fields
const form = useForm({
  external_id: '',
  name: '',
  email: '',
  phone: '',
  gender: '',
  birth_year: null,
  organization: '',
  department: '',
  role_title: '',
  location: '',
  demographics: {},
  consent: false
})

// Submit form function
const submitForm = () => {
  // Set consent timestamp when form is submitted
  const formData = {
    ...form.data(),
    consent_at: form.consent ? new Date().toISOString() : null
  }

  form.post(route('survey.register'), {
    onSuccess: () => {
      // Handle successful registration
      console.log('Registration successful')
    },
    onError: (errors) => {
      // Handle validation errors
      console.error('Registration failed:', errors)
    }
  })
}
</script>