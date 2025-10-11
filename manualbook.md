# Manual Book Aplikasi Survey App

> Versi 1.0 — Laravel + Inertia.js + Vue 3 + Tailwind (DaisyUI)
> Autentikasi: Laravel Sanctum

---

## Daftar Isi
- [Pendahuluan](#pendahuluan)
- [Instalasi & Setup](#instalasi--setup)
- [Arsitektur Sistem](#arsitektur-sistem)
  - [Routing](#routing)
  - [Middleware](#middleware)
  - [Layout & Komponen](#layout--komponen)
  - [Halaman Frontend](#halaman-frontend)
- [Autentikasi & Otorisasi](#autentikasi--otorisasi)
- [Fitur Utama](#fitur-utama)
  - [Manajemen Survey](#manajemen-survey)
  - [Struktur Survey](#struktur-survey)
  - [Pengisian Survey](#pengisian-survey)
  - [Kategori Hasil](#kategori-hasil)
  - [Manajemen Pengguna](#manajemen-pengguna)
  - [UI & UX](#ui--ux)
- [Alur Pengguna](#alur-pengguna)
  - [Responden](#responden)
  - [Surveyor](#surveyor)
  - [Admin](#admin)
- [Panduan Admin](#panduan-admin)
- [API & Integrasi](#api--integrasi)
- [Manajemen Data & Model](#manajemen-data--model)
- [Konfigurasi & Lingkungan](#konfigurasi--lingkungan)
- [Operasional & Pemeliharaan](#operasional--pemeliharaan)
- [Troubleshooting](#troubleshooting)
- [Lampiran](#lampiran)

---

## Pendahuluan
Aplikasi Survey App memfasilitasi pembuatan, pengelolaan, dan analisis survei dengan peran admin dan surveyor, serta proses pengisian oleh responden.

### Komponen Utama
- Backend: Laravel dengan REST API dan Inertia untuk server-side rendering reaktif.
- Frontend: Vue 3 + Tailwind CSS (DaisyUI) untuk UI modern.
- Autentikasi: Laravel Sanctum untuk sesi dan token API.
- Database: MySQL/MariaDB.

---

## Instalasi & Setup

### Prasyarat
- PHP `8.2+`, Composer
- Node.js `18+`, npm
- MySQL/MariaDB

### Langkah Instalasi
1. Duplikasi `./env.example` ke `.env` dan sesuaikan:
   - `APP_URL`, `APP_ENV`, `APP_KEY`
   - Koneksi database: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Generate key (jika belum):
   ```bash
   php artisan key:generate
   ```
4. Migrasi dan seeding:
   ```bash
   php artisan migrate --seed
   ```
5. Jalankan server dan Vite dev:
   ```bash
   php artisan serve
   npm run dev
   ```

### Konfigurasi Tambahan
- Sanctum: route `GET /sanctum/csrf-cookie` menyiapkan cookie CSRF.
- Vite: konfigurasi di `vite.config.js`.
- Mail (reset password): `config/mail.php` dan environment `MAIL_*` di `.env`.

---

## Arsitektur Sistem

### Routing

#### Web (`routes/web.php`)
- Publik:
  - `/` — halaman welcome/landing.
  - Alur survey guest: `/entry`, `/survey/respondent-data`, `/survey/questions`, `/survey/result`.
- Autentikasi:
  - `GET/POST /login`, `POST /logout`.
  - Reset password: `GET/POST /forgot-password`, `GET /reset-password/{token}`, `POST /reset-password`.
- Dashboard (middleware `auth`):
  - `/dashboard` — ringkasan statistik.
  - `/dashboard/survey` — daftar survey (index/list).
  - `/dashboard/survey/{survey}` — detail survey.
  - `/dashboard/survey/{survey}/manage` — pengelolaan section & question.
  - `/dashboard/survey/{survey}/responses` — daftar respon, ekspor, detail.
  - `/dashboard/user-management` — manajemen pengguna (khusus admin via `role:admin`).
  - `/dashboard/change-password` — ubah password.

#### API (`routes/api.php`)
- Terproteksi `auth:sanctum` (Bearer token):
  - `users` — CRUD pengguna (`UserController`).
  - `surveys` — CRUD survey (`SurveyController`).
  - `surveys.sections` — CRUD section (`SurveySectionController`).
  - `sections.questions` — CRUD pertanyaan (`QuestionController`).
  - `surveys/{survey}/statistics` — statistik survey.
  - `result-categories` & `rules` — kategori hasil dan aturan (`ResultCategoryController`, `ResultCategoryRuleController`).
  - `surveys/{survey}/data` & `responses` — data survey dan pengisian via API (`SurveyTakeController`).
- Publik:
  - Data wilayah: `/api/wilayah/provinces`, `/regencies/{code}`, `/districts/{code}`, `/villages/{code}`.
- Terproteksi token proses:
  - `POST /api/survey/question-partials` (middleware `survey.token`) — submit parsial jawaban.

### Middleware
- `auth`, `guest`, `auth:sanctum` — kontrol akses dasar.
- `role:admin` — akses halaman manajemen pengguna.
- `no.cache` — mencegah caching.
- `guest.survey`, `survey.token` — mengatur akses alur survey untuk responden.

### Layout & Komponen
- `DashboardLayout` — layout utama dashboard, memuat `Header`, `Sidebar`, `Toast` dan slot konten.
- Komponen UI:
  - `Sidebar` — navigasi berdasarkan role.
  - `Header` — header aplikasi dengan aksi cepat.
  - `Toast` — notifikasi.
  - `DataTable` — tabel data dinamis dengan paginasi.
  - `PageHeader`, `ConfirmationModal`, dsb.

### Halaman Frontend
- Landing & Entry:
  - `Welcome.vue` — halaman landing dengan daftar survei publik dan CTA.
  - `Entry.vue` — input kode survey untuk memperoleh token sesi.
- Alur Survey (responden):
  - `Survey/RespondentData.vue` — form data responden.
  - `Survey/Questions.vue` — isi pertanyaan (mendukung submit parsial).
  - `Survey/Result.vue` — hasil/feedback berdasarkan skor/kategori.
- Dashboard (surveyor/admin):
  - `Dashboard/Index.vue` — statistik pengguna/survey.
  - `Dashboard/Survey/Index.vue` — manajemen daftar survey.
  - `Dashboard/Survey/Show.vue` — detail survey, sections, quick actions.
  - `Dashboard/Survey/Manage.vue` — CRUD section & question dengan konfirmasi hapus.
  - `Dashboard/Survey/Response.vue` — daftar respon, filter, statistik, ekspor.
  - `Dashboard/UserManagement/Index.vue` — kelola pengguna (admin).

---

## Autentikasi & Otorisasi

### Login
- Form `POST /login` (validasi oleh `AuthController::login`).
- Setelah berhasil: sesi diregenerasi untuk keamanan.
- Mendukung opsi "remember me" (jika diterapkan di form).

### Logout
- `POST /logout` — mengakhiri sesi pengguna.

### Reset Password
- `GET/POST /forgot-password` — permintaan tautan reset.
- `GET /reset-password/{token}` — halaman reset.
- `POST /reset-password` — submit password baru.

### Token API
- `POST /api/token` — menghasilkan token untuk integrasi.
- Gunakan header `Authorization: Bearer <token>` untuk akses endpoint terproteksi.

### Role-Based Access
- `RoleMiddleware` memeriksa peran `admin` dan `surveyor`.
- Admin: akses penuh termasuk user management.
- Surveyor: kelola survey milik sendiri, tanpa akses halaman admin-only.

---

## Fitur Utama

### Manajemen Survey
- CRUD survey via API (`Route::apiResource('surveys')` → `SurveyController`).
- Status (`SurveyStatus`): `draft`, `active`, `closed`.
- Visibility (`SurveyVisibility`): `private`, `link`, `public`.
- Statistik per survey dan dashboard (jumlah survey, respon, selesai, dll.).

### Struktur Survey
- Sections — pengelompokan pertanyaan, diatur urutan dan relasi ke survey.
- Questions — tipe (`QuestionType`), required, order; relasi `choices` untuk pilihan.
- Choices — label, value, score, order (untuk single/multiple choice).

### Pengisian Survey
- Diatur oleh `SurveyProcessController` (web) dan `SurveyTakeController` (API):
  - Entry: memperoleh token (middleware `guest.survey`).
  - Data Responden: `GET/POST /survey/respondent-data`.
  - Pertanyaan: mendukung submit parsial (`/api/survey/question-partials`).
  - Penyelesaian: status respon (`ResponseStatus`) di-update ke `completed`.
- Locking proses (`SurveyLock`) untuk mencegah konflik pengisian.

### Kategori Hasil
- `ResultCategory` — per survey atau per section (`owner_type` `survey`/`survey_section`).
- `ResultCategoryRule` — aturan scoring (operation `lt`, `gt`, `else`), deskripsi, warna.
- `ResponseScore` — agregasi skor respon dan pemetaan ke kategori.

### Manajemen Pengguna
- `UserController` — daftar, tambah, edit, hapus user via API.
- Peran (`UserRole`): `admin`, `surveyor`.
- Ubah password: `ChangePasswordController::update` via `/dashboard/change-password`.

### UI & UX
- Judul halaman konsisten via `<Head title="..." />` di semua page penting.
- Tabel data dinamis (`DataTable.vue`) dengan paginasi dan kontrol aksi.
- Sidebar adaptif berdasarkan role.
- Modal konfirmasi hapus di Manage (sections & questions).

---

## Alur Pengguna

### Responden
1. Akses `/` → klik "Ikuti Survey" → ke `/entry`.
2. Masukkan kode survey → diarahkan ke form data responden.
3. Isi pertanyaan (dengan kemungkinan submit parsial jika diperlukan).
4. Lihat hasil/feedback pada halaman `Survey/Result.vue`.

### Surveyor
1. Login → `/dashboard` (ringkas statistik).
2. Buat/kelola survey di `/dashboard/survey`.
3. Atur sections dan pertanyaan di `/dashboard/survey/{id}/manage`.
4. Lihat respon dan ekspor di `/dashboard/survey/{id}/responses`.

### Admin
1. Semua alur surveyor.
2. Akses user management di `/dashboard/user-management`.

---

## Panduan Admin
- Masuk sebagai admin.
- Buka menu "User Management" di sidebar.
- Tindakan yang tersedia:
  - Tambah user baru.
  - Edit data dan role.
  - Hapus user (dengan konfirmasi).
- Ganti password via `POST /dashboard/change-password` — isi password lama, baru, dan konfirmasi.

---

## API & Integrasi

### Autentikasi API
- `POST /api/token` untuk mendapatkan token.
- Sertakan `Authorization: Bearer <token>` pada setiap request terproteksi.

### Endpoint Utama
- Pengguna:
  - `GET/POST/PUT/DELETE /api/users`
- Survey:
  - `GET/POST/PUT/DELETE /api/surveys`
  - `GET /api/surveys/{survey}/statistics`
  - `GET /api/surveys/{survey}/data`
  - `POST /api/surveys/{survey}/responses`
- Struktur:
  - `GET/POST/PUT/DELETE /api/surveys/{survey}/sections`
  - `GET/POST/PUT/DELETE /api/sections/{section}/questions`
- Kategori & Rule:
  - `GET /api/surveys/{survey}/result-categories`
  - `GET /api/sections/{section}/result-categories`
  - `GET/POST/PUT/DELETE /api/result-categories/{resultCategory}/rules`
- Wilayah:
  - `GET /api/wilayah/provinces`
  - `GET /api/wilayah/regencies/{code}`
  - `GET /api/wilayah/districts/{code}`
  - `GET /api/wilayah/villages/{code}`
- Proses Survey:
  - `POST /api/survey/question-partials` (with `survey.token`).

---

## Manajemen Data & Model
- `User` — peran (`UserRole`), relasi survey.
- `Survey` — status (`SurveyStatus`), visibility (`SurveyVisibility`), relasi sections & responses.
- `SurveySection` — pengelompokan pertanyaan.
- `Question` — tipe (`QuestionType`), required, order; relasi `choices`, `answers`.
- `Choice` — opsi pada pertanyaan pilihan, skor dan urutan.
- `Respondent` — data peserta (terkait `Response`).
- `Response` — status (`ResponseStatus`), `submitted_at`, agregasi skor, relasi `answers`.
- `Answer` — jawaban teks/angka/json, relasi ke `question`/`choice`.
- `ResultCategory` & `ResultCategoryRule` — klasifikasi hasil dengan rule operasi `lt`, `gt`, `else`.
- `ResponseScore` — skor per kategori/response.
- `Location` — data lokasi responden untuk agregasi & filter.
- `SurveyLock` — locking proses agar aman & konsisten.

---

## Konfigurasi & Lingkungan

### Environment (`.env`)
- Aplikasi: `APP_URL`, `APP_ENV`, `APP_KEY`
- Database: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Sanctum/Cookie: domain dan stateful.
- Mail: konfigurasi SMTP (`MAIL_*`) untuk reset password.

### Config (`config/`)
- `auth.php`, `sanctum.php` — pengaturan autentikasi.
- `services.php` — konfigurasi integrasi eksternal (jika ada).

### Frontend
- Pastikan setiap page memiliki `<Head title="..." />` untuk SEO/title konsisten.
- Tema dan utilitas Tailwind diatur di `tailwind.config.js`.

---

## Operasional & Pemeliharaan
- Deployment:
  - Build assets: `npm run build`.
  - Optimisasi cache: `php artisan config:cache`, `route:cache`, `view:cache`.
- Backup: lakukan backup database terjadwal.
- Monitoring: periksa log di `storage/logs`, pantau error rate.
- Security:
  - Hindari rotasi `APP_KEY` tanpa re-encrypt data.
  - Pastikan middleware `auth`/`role` diterapkan konsisten.

---

## Troubleshooting
- Autentikasi gagal:
  - Panggil `GET /sanctum/csrf-cookie` sebelum request form.
  - Verifikasi kredensial dan role user.
- 403 pada dashboard:
  - Role tidak sesuai; gunakan akun admin untuk user management.
- API 500:
  - Cek validasi request, constraint database, dan log server.
- UI tidak update:
  - Pastikan Vite dev server aktif dan HMR berjalan (lihat log terminal).

---

## Lampiran

### Konvensi Enum
- `SurveyStatus`: `draft`, `active`, `closed`.
- `SurveyVisibility`: `private`, `link`, `public`.
- `QuestionType`: `short_text`, `long_text`, `single_choice`, `multiple_choice`, `number`, `date`.
- `ResponseStatus`: `started`, `in_progress`, `completed`, `abandoned`.
- `OperationType`: `lt`, `gt`, `else`.
- `UserRole`: `admin`, `surveyor`.

### Komponen Kunci
- `DashboardLayout`, `Sidebar`, `Header`, `Toast`, `DataTable`, `PageHeader`, `ConfirmationModal`.

---

## Catatan
- Dokumen ini merangkum arsitektur dan alur utama aplikasi. Untuk detail implementasi tiap controller/model/komponen, lihat direktori `app/` dan `resources/js/` pada repository.
        