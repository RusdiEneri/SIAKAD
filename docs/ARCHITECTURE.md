# Architecture & Technical Specification Document - SIAKAD

Dokumen arsitektur ini menetapkan fondasi teknis, batas-batas teknologi terkunci, konvensi penulisan kode, serta alur eksekusi aplikasi SIAKAD.

---

## 1. Stack Terkunci (Locked Tech Stack)
Berdasarkan hasil inspeksi langsung terhadap repositori dan lingkungan eksekusi local:

- **PHP Version**: `8.2.12` (Strict Types, Match Expressions, Backed Enums).
- **Framework**: `Laravel 12.x` (`laravel/framework: ^12.0`).
- **Admin Panel**: `Filament 3.x` (`filament/filament: ^3.0`).
- **Database**: `MySQL` (`DB_CONNECTION=mysql`, `DB_DATABASE=siakad`).
- **Timezone**: `Asia/Jakarta` (WIB) (Dikonfigurasi di `config/app.php` dan `.env`).
- **Locale**: `id` (Bahasa Indonesia).
- **Role & Authorization**: Manual RBAC (`roles` + `role_user`) **TANPA** package pihak ketiga seperti `spatie/laravel-permission`.

*Catatan Kritis*: Dilarang melakukan upgrade/downgrade versi package di atas atau menambah package composer baru tanpa konfirmasi dan izin tertulis dari user.

---

## 2. Layering Architecture & Separation of Concerns

Aplikasi menerapkan pola arsitektur **Clean Layering** untuk memastikan logika bisnis tidak tercampur dengan UI Layer (Filament Panel) maupun HTTP Layer.

```
┌────────────────────────────────────────────────────────┐
│                   Filament UI Layer                    │
│    (Resources, Pages, Widgets, Forms, Tables, Actions) │
└───────────────────────────┬────────────────────────────┘
                            │ (Form Input / Action Trigger)
                            ▼
┌────────────────────────────────────────────────────────┐
│                     Service Layer                      │
│   (EnrollmentService, IpkCalculator, ApprovalService)  │
│  * Eksekusi Aturan Bisnis R1 - R14                     │
│  * Transaksi Database (DB::transaction)                │
└───────────────────────────┬────────────────────────────┘
                            │ (Eloquent ORM)
                            ▼
┌────────────────────────────────────────────────────────┐
│              Domain & Data Access Layer                │
│    (Models, Enums, Scopes, Relations, Migrations)      │
└────────────────────────────────────────────────────────┘
```

1. **Service Layer (`app/Services`)**: Tempat bersemayam seluruh Aturan Bisnis R1–R14 (misalnya: `EnrollmentService`, `IpkCalculator`, `ScheduleConflictChecker`, `KrsApprovalService`). Service layer dapat dipanggil dari mana saja (Filament Actions, Form Requests, Artisan Commands, atau Controller).
2. **Enums (`app/Enums`)**: Semua tipe data terbatas (Status KRS, Hari, Nilai Huruf, Jenis Semester, Jenjang, Role Name) menggunakan PHP 8.1+ Backed Enums (`string`).
3. **Configuration (`config/siakad.php`)**: Tempat penyimpanan angka-angka ajaib (magic numbers), batas tier SKS, mapping bobot nilai, dan flag gate UKT.
4. **Policies (`app/Policies`)**: Menangani otorisasi hak akses berdasarkan role dan kepemilikan data (R11).
5. **Filament UI Layer (`app/Filament`)**: Hanya bertindak sebagai pengumpul input pengguna dan penyaji UI. **DILARANG HARAM** menulis query kompleks atau aturan bisnis di dalam class Filament Resource/Form/Table!

---

## 3. Struktur Directory (Folder Map)

```
SIAKAD/
├── app/
│   ├── Enums/
│   │   ├── HariEnum.php
│   │   ├── JenjangEnum.php
│   │   ├── KrsStatusEnum.php
│   │   ├── NilaiHurufEnum.php
│   │   ├── RoleEnum.php
│   │   ├── SemesterJenisEnum.php
│   │   └── StatusMahasiswaEnum.php
│   ├── Filament/
│   │   ├── Pages/
│   │   ├── Resources/
│   │   │   ├── DosenResource.php
│   │   │   ├── KelasResource.php
│   │   │   ├── KrsResource.php
│   │   │   ├── MahasiswaResource.php
│   │   │   ├── MataKuliahResource.php
│   │   │   ├── NilaiResource.php
│   │   │   ├── PembayaranResource.php
│   │   │   ├── ProdiResource.php
│   │   │   ├── SemesterResource.php
│   │   │   └── UserResource.php
│   │   └── Widgets/
│   ├── Models/
│   │   ├── Dosen.php
│   │   ├── Jadwal.php
│   │   ├── Kelas.php
│   │   ├── Krs.php
│   │   ├── KrsDetail.php
│   │   ├── Mahasiswa.php
│   │   ├── MataKuliah.php
│   │   ├── Nilai.php
│   │   ├── Pembayaran.php
│   │   ├── Prodi.php
│   │   ├── Role.php
│   │   ├── Semester.php
│   │   └── User.php
│   ├── Policies/
│   │   ├── KrsPolicy.php
│   │   ├── NilaiPolicy.php
│   │   └── ...
│   └── Services/
│       ├── EnrollmentService.php
│       ├── IpkCalculator.php
│       ├── KrsApprovalService.php
│       └── ScheduleConflictChecker.php
├── config/
│   ├── app.php
│   └── siakad.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
└── docs/
    ├── ARCHITECTURE.md
    ├── DESIGN.md
    ├── ERD.md
    ├── PLAN.md
    ├── PRD.md
    ├── RULES.md
    └── SCHEMA.md
```

---

## 4. Tracing Alur Eksekusi Contoh Request

### Tracing 1: Mahasiswa Menambah Kelas ke KRS (Pengambilan KRS)
1. **User Action**: Mahasiswa menekan tombol "Tambah Kelas" pada Form KRS di Panel Filament.
2. **Filament Resource Action**: Filament memanggil method `takeCourse(Mahasiswa $mhs, Kelas $kelas)` di `EnrollmentService`.
3. **Service Layer Validation**:
   - `EnrollmentService` memeriksa **R8**: Apakah status Mahasiswa `aktif`?
   - `EnrollmentService` memanggil `IpkCalculator` untuk mendapatkan IPS semester lalu -> hitung Max SKS sesuai **R1** (misal 21 SKS). Memeriksa total SKS jika kelas baru ditambahkan.
   - `EnrollmentService` mengecek **R5**: Apakah semester penawaran mata kuliah sesuai dengan jenis semester aktif?
   - `EnrollmentService` mengecek **R7**: Apakah mahasiswa sudah pernah lulus MK ini atau sudah mengambil kelas MK ini pada KRS semester aktif?
   - `EnrollmentService` mengecek **R2**: Memeriksa tabel `mata_kuliah_prasyarat` vs `nilais` milik mahasiswa. Apakah semua prasyarat lulus (`lulus = true`)?
   - `EnrollmentService` mengecek **R4**: Memeriksa jumlah `krs_details` kelas vs `kelas.kapasitas`.
   - `EnrollmentService` memanggil `ScheduleConflictChecker` (**R3**): Memeriksa hari & jam `jadwals` kelas baru vs `jadwals` dari kelas-kelas yang sudah ada di KRS semester aktif.
4. **Execution**: Jika seluruh validasi lolos, buat record `krs_details` dalam `DB::transaction()` dan update `krs.total_sks`. Jika ada 1 aturan yang gagal, lempar `ValidationException` dengan pesan error spesifik.

---

### Tracing 2: Persetujuan KRS oleh Kaprodi / Dosen Wali
1. **User Action**: Kaprodi membuka list KRS `menunggu` dan menekan tombol "Approve".
2. **Policy Check**: `KrsPolicy::approve()` mengecek **R11**: Apakah User adalah Kaprodi dari Prodi Mahasiswa pemilik KRS tersebut?
3. **Service Execution**: Panggil `KrsApprovalService::approve(Krs $krs, User $approver)`.
4. **UKT Gate Validation**:
   - `KrsApprovalService` mengecek `config('siakad.ukt_gate_enabled')` (**R6**).
   - Memeriksa record `pembayarans` Mahasiswa untuk semester KRS tersebut. Jika `status != 'lunas'`, batalkan persetujuan dan kembalikan exception.
5. **State Update**: Jika lunas, ubah status `krs.status` menjadi `disetujui`, set `krs.disetujui_oleh = $approver->id`, dan simpan timestamp.

---

## 5. Konvensi Kode (Coding Standards & Constraints)
- **Type Hinting**: Semua method WAJIB memiliki type hint parameter dan return type declaration.
- **Strict Typing**: Gunakan `declare(strict_types=1);` di setiap file service/enum baru.
- **Soft Deletes**: Semua model transaksi (`Krs`, `KrsDetail`, `Nilai`, `Pembayaran`) WAJIB menggunakan trait `Illuminate\Database\Eloquent\SoftDeletes`.
- **Custom RBAC Methods**:
  - Model `User` memiliki relasi `public function roles(): BelongsToMany` dan helper `public function hasRole(string|array $roles): bool`.
  - Panel Filament gating via `User::canAccessPanel(Panel $panel): bool`.
