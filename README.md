# 🎓 SIAKAD - Sistem Informasi Akademik Perguruan Tinggi

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-D97706?style=for-the-badge&logo=laravel&logoColor=white)](https://filamentphp.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tests](https://img.shields.io/badge/Tests-25%20Passed%20(85%20Assertions)-10B981?style=for-the-badge&logo=githubactions&logoColor=white)](#-pengujian-otomatis-automated-testing)

SIAKAD adalah Sistem Informasi Akademik berbasis web yang dirancang dengan disiplin *software engineering* tinggi untuk mengelola seluruh siklus akademik perguruan tinggi: pengelolaan Master Data, Pengajuan & Persetujuan Kartu Rencana Studi (KRS), Cek Bentrok Jadwal, Penegakan Prasyarat & Gate UKT, Input Nilai, Kalkulasi IPS/IPK Terpusat, Rekap Cetak Laporan (KRS, KHS, Transkrip), hingga Portal Mandiri Mahasiswa & Dosen.

---

## 🔑 Akun & Peran Pengguna (Role Credentials)

Seluruh akun pengujian dasar telah di-seed secara otomatis ke dalam database. Gunakan kredensial di bawah ini untuk menguji hak akses dan otorisasi multi-role:

> 🔐 **Password Default Seluruh Akun**: `password`

| Peran (Role) | Email | Password | Hak Akses & Cakupan Otorisasi |
| :--- | :--- | :--- | :--- |
| **Admin System** | `admin@siakad.ac.id` | `password` | **Full Access**: Pengelolaan seluruh Master Data, User, Role, Semester, Mata Kuliah, serta Laporan Akademik tanpa batasan query scoping. |
| **Kaprodi TIF** | `kaprodi@siakad.ac.id` | `password` | **Prodi Scope**: Verifikasi/Approval KRS Mahasiswa TIF, pengelolaan Mata Kuliah & Seksi Kelas prodi, monitoring mahasiswa anak prodi. |
| **Dosen Pengampu** | `dosen@siakad.ac.id` | `password` | **Lecturer Scope**: Pengampuan seksi kelas, input nilai akhir mahasiswa peserta kelas, dan wali akademik mahasiswa anak bimbingan. |
| **Mahasiswa** | `mahasiswa@siakad.ac.id` | `password` | **Student Scope**: Penyusunan KRS interaktif, monitoring status approval, cek tagihan UKT, pratinjau KHS, dan Transkrip Kumulatif personal. |
| **Staf Keuangan** | `keuangan@siakad.ac.id` | `password` | **Finance Scope**: Konfirmasi & verifikasi status pembayaran UKT mahasiswa (Syarat Gate Approval KRS). |

---

## 📋 Prasyarat Sistem (Prerequisites)

Sebelum melakukan instalasi, pastikan lingkungan server atau *development environment* Anda telah memenuhi spesifikasi berikut:

- **PHP**: Versi `>= 8.2` (dengan ekstensi: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- **Database**: MySQL `>= 8.0` atau MariaDB `>= 10.4`
- **Composer**: Versi `>= 2.x`
- **Node.js**: Versi `>= 18.x` & **npm**: Versi `>= 9.x`
- **Web Server**: Laragon / XAMPP / PHP Built-in CLI Server

---

## ⚙️ Panduan Instalasi Lengkap (Step-by-Step Installation)

Ikuti langkah-langkah di bawah ini untuk memasang dan menjalankan SIAKAD di lingkungan lokal Anda:

### 1. Clone / Siapkan Repository
Masuk ke direktori kerja web server Anda (misal `C:\laragon\www\SIAKAD`):
```bash
git clone <repository-url> SIAKAD
cd SIAKAD
```

### 2. Install Dependensi PHP (Composer)
```bash
composer install
```

### 3. Install Dependensi Frontend (NPM)
```bash
npm install
```

### 4. Konfigurasi Environment (`.env`)
Salin file konfigurasi contoh menjadi `.env`:
```bash
cp .env.example .env
```
Pastikan pengaturan koneksi database pada `.env` sudah sesuai dengan lingkungan MySQL Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siakad
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Buat Database MySQL
Buat database bernama `siakad` di MySQL Anda (via Laragon, phpMyAdmin, atau terminal):
```sql
CREATE DATABASE siakad;
```

### 7. Jalankan Migrasi & Seeding Data
Perintah ini akan membuat 15 tabel database beserta data seeder (Role, User, Prodi, Dosen, Mahasiswa, Mata Kuliah, Semester, Kelas, dan Pembayaran):
```bash
php artisan migrate:fresh --seed
```

### 8. Build Aset Frontend & Publish Aset Filament
```bash
npx vite build
php artisan filament:assets
php artisan optimize:clear
```

### 9. Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi dapat diakses melalui browser pada URL:
- **Panel Admin Filament**: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)
- **Portal Mandiri Mahasiswa & Dosen**: [http://127.0.0.1:8000/portal](http://127.0.0.1:8000/portal)

---

## 💾 Panduan Backup & Import Database (Database Operations)

### A. Cara Export / Backup Database `siakad`

#### Metode 1: Menggunakan MySQL CLI (Recommended)
Jalankan perintah berikut di terminal / command prompt:
```bash
mysqldump -u root -p siakad > backup_siakad.sql
```

#### Metode 2: Menggunakan phpMyAdmin
1. Buka phpMyAdmin di browser (`http://localhost/phpmyadmin`).
2. Pilih database `siakad` di panel sebelah kiri.
3. Klik tab **Export**, pilih metode **Quick**, lalu klik **Export**.

---

### B. Cara Import / Restore Database `siakad`

#### Metode 1: Menggunakan MySQL CLI (Recommended)
1. Buat database `siakad` jika belum ada:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS siakad;"
   ```
2. Import file dump SQL:
   ```bash
   mysql -u root -p siakad < backup_siakad.sql
   ```

#### Metode 2: Menggunakan phpMyAdmin
1. Buka phpMyAdmin dan buat database baru bernama `siakad`.
2. Klik database `siakad`, lalu pilih tab **Import**.
3. Pilih file `backup_siakad.sql` dari komputer Anda, lalu klik **Import**.

#### Metode 3: Menggunakan Fresh Seeder (Re-generate Data Otomatis)
Jika ingin mengembalikan database ke kondisi *clean default* beserta seeder awal:
```bash
php artisan migrate:fresh --seed
```

---

## 📐 Aturan Bisnis Akademik (Business Rules R1 - R14)

SIAKAD menerapkan 14 Aturan Bisnis Akademik ketat yang terekapsulasi di dalam `app/Services/` & `app/Policies/`:

- **R1: Jatah SKS Maksimum**: Ditentukan berdasarkan Tier IPS semester sebelumnya (`IPS < 2.00` $\rightarrow$ 18 SKS, `2.00-2.99` $\rightarrow$ 20 SKS, `3.00-3.49` $\rightarrow$ 22 SKS, `≥ 3.50` $\rightarrow$ 24 SKS).
- **R2: Prasyarat Mata Kuliah**: Pengambilan MK tingkat lanjut mewajibkan kelulusan MK prasyarat.
- **R3: Cek Bentrok Jadwal**: Pengambilan kelas mengecek bentrok hari dan irisan jam (mulai–selesai) via `ScheduleConflictChecker`.
- **R4: Kapasitas Kelas**: Pendaftaran kelas ditolak jika kuota peserta sudah penuh.
- **R5: Kesesuaian Penawaran Semester**: Hanya kelas pada semester aktif yang dapat diambil.
- **R6: Gate UKT**: Approval KRS terkunci jika status pembayaran UKT belum `LUNAS` (dapat dikonfigurasi via `config/siakad.php`).
- **R7: Anti-Duplikasi & Anti-Retake Lulus**: Mencegah pendaftaran ganda pada kelas yang sama atau MK yang sudah dinyatakan lulus.
- **R8: Status Mahasiswa Aktif**: Mahasiswa non-aktif/cuti/DO diblokir dari penyusunan KRS.
- **R9 & R10: Kalkulasi IPS & IPK Terpusat**: Dihitung terpusat via `IpkCalculator` ($\frac{\sum \text{Bobot} \times \text{SKS}}{\sum \text{SKS}}$).
- **R11 & R12: Otorisasi Policy & Multi-Role Query Scoping**: Otorisasi via Laravel Policies & saringan data `getEloquentQuery()` per role di Filament.
- **R13: Audit Trail Minimal**: Pencatatan ID approver (`disetujui_oleh`) dan timestamp modifikasi.
- **R14: Soft Deletes**: Penggunaan fitur Soft Delete pada seluruh tabel transaksional.

---

## 🧪 Pengujian Otomatis (Automated Testing)

Proyek ini dilengkapi dengan suite pengujian otomatis PHPUnit yang menguji database, layanan bisnis R1–R14, policy otorisasi, controller laporan, dan portal Livewire.

Untuk menjalankan seluruh test suite:
```bash
php artisan test
```

### Hasil Pengujian:
```text
  PASS  Tests\Unit\ExampleTest
  PASS  Tests\Unit\FilamentMasterDataTest
  PASS  Tests\Unit\SiakadDatabaseTest
  PASS  Tests\Unit\SiakadPolicyTest
  PASS  Tests\Unit\SiakadPortalTest
  PASS  Tests\Unit\SiakadReportsTest
  PASS  Tests\Unit\SiakadServicesTest
  PASS  Tests\Feature\ExampleTest

  Tests:    25 passed (85 assertions)
  Duration: 1.97s
```

---

## 🗂️ Peta Struktur Direktori Utama

```
SIAKAD/
├── app/
│   ├── Enums/               # Backed Enums (RoleEnum, KrsStatusEnum, NilaiHurufEnum, dll)
│   ├── Filament/            # Admin Panel Filament (Resources, Pages, Widgets)
│   │   ├── Resources/       # Resource Master Data & Transaksi Akademik
│   │   └── Widgets/         # SiakadStatsOverview Widget per Role
│   ├── Http/Controllers/    # ReportController (Render Cetak KRS, KHS, Transkrip)
│   ├── Livewire/Portal/     # Livewire Component Dashboard & Portal Mandiri
│   ├── Models/              # Model Eloquent lengkap dengan relasi & SoftDeletes
│   ├── Policies/            # Policy Class Otorisasi Laravel (UserPolicy, KrsPolicy, dll)
│   └── Services/            # Service Logika Bisnis (EnrollmentService, IpkCalculator, dll)
├── config/
│   └── siakad.php           # Konfigurasi Aturan SKS, Grade Points, & Flag Gate UKT
├── database/
│   ├── migrations/          # 13 Migration Files untuk 15 Tabel Database
│   └── seeders/             # RoleSeeder & DatabaseSeeder
├── docs/                    # Dokumentasi Arsitektur (PRD, ERD, Schema, Rules, Plan)
├── resources/
│   ├── views/
│   │   ├── layouts/         # Layout Portal Mandiri (Glassmorphic Theme)
│   │   ├── livewire/        # View Blade Livewire Portal Component
│   │   └── reports/         # Template Blade Cetak KRS, KHS, & Transkrip
├── routes/
│   └── web.php              # Route Web, Portal, & Laporan PDF/Print
└── tests/
    └── Unit/                # Automated PHPUnit Test Suite
```

---

## 📄 Lisensi & Kontribusi

Proyek SIAKAD ini dikembangkan secara disiplin dengan kerangka kerja [Laravel](https://laravel.com) di bawah lisensi [MIT license](https://opensource.org/licenses/MIT).
