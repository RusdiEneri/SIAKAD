# Implementation Roadmap & Phase Plan - SIAKAD

Dokumen ini memetakan seluruh tahapan eksekusi pembangunan SIAKAD dari Fase 0 hingga Fase 6 beserta deliverable dan Definition of Done (DoD) per fase.

---

## FASE 0: Perencanaan Arsitektur & Dokumentasi (Fase Sekarang)
- **Status**: Sedang Dikerjakan (In Progress)
- **Deliverables**:
  - [x] `docs/PRD.md` - Product Requirement Document
  - [x] `docs/ERD.md` - Diagram ERD Mermaid
  - [x] `docs/SCHEMA.md` - Data Dictionary & Spesifikasi Skema Database
  - [x] `docs/RULES.md` - Aturan Bisnis R1 - R14 & Kasus Uji
  - [x] `docs/ARCHITECTURE.md` - Spesifikasi Arsitektur & Stack Terkunci
  - [x] `docs/DESIGN.md` - UI/UX Design & Outlines Panel
  - [x] `docs/PLAN.md` - Implementation Roadmap & Phase Plan
  - [x] Menyajikan Ringkasan Arsitektur + Daftar Asumsi/Ambiguitas + Meminta Approval User.
- **Definition of Done (DoD)**: Seluruh 7 dokumen terisi lengkap dan disetujui oleh User sebelum penulisan kode aplikasi dimulai.

---

## FASE 1: Fondasi Basis Data & Service Core
- **Deliverables**:
  - Konfigurasi `config/siakad.php` (tier SKS, mapping nilai->bobot, flag UKT gate).
  - PHP Backed Enums di `app/Enums/` (`RoleEnum`, `HariEnum`, `JenjangEnum`, `StatusMahasiswaEnum`, `SemesterJenisEnum`, `KrsStatusEnum`, `NilaiHurufEnum`).
  - Seluruh file Migration untuk 15 tabel (`users`, `roles`, `role_user`, `prodis`, `dosens`, `mahasiswas`, `mata_kuliahs`, `mata_kuliah_prasyarat`, `semesters`, `kelas`, `jadwals`, `krs`, `krs_details`, `nilais`, `pembayarans`).
  - Seluruh Model Eloquent di `app/Models/` lengkap dengan relasi, casts, dan soft deletes.
  - Database Factories & DatabaseSeeder minimal untuk pengujian.
- **Definition of Done (DoD)**: Perintah `php artisan migrate:fresh --seed` berjalan sukses 100% tanpa error, dan relasi Eloquent teruji via unit test / tinker.

---

## FASE 2: Filament Resources - Master Data Management
- **Deliverables**:
  - Custom RBAC setup (Method `roles()` dan `hasRole()` pada model `User`).
  - Filament Resources untuk Master Data:
    - `UserResource` & `RoleResource`
    - `ProdiResource`
    - `DosenResource`
    - `MahasiswaResource`
    - `MataKuliahResource` (dengan pengelola pivot `mata_kuliah_prasyarat`)
    - `SemesterResource`
- **Definition of Done (DoD)**: Admin dapat melakukan CRUD lengkap pada seluruh master data via Admin Panel Filament tanpa error.

---

## FASE 3: Service Logika Bisnis & Filament Resources Transaksi
- **Deliverables**:
  - Core Business Services di `app/Services/`:
    - `IpkCalculator` (Penghitungan IPS/IPK terpusat R9, R10).
    - `ScheduleConflictChecker` (Deteksi bentrok jam/hari R3).
    - `EnrollmentService` (Eksekusi pengambilan KRS dengan penegakan R1, R2, R4, R5, R7, R8).
    - `KrsApprovalService` (Alur persetujuan KRS & Gate UKT R6).
  - Filament Resources Transaksi:
    - `KelasResource` & `JadwalResource`
    - `KrsResource` (Form penyusunan KRS Mahasiswa & Approval Action)
    - `NilaiResource` (Form input nilai dosen)
    - `PembayaranResource` (Pencatatan UKT oleh Keuangan)
- **Definition of Done (DoD)**: Seluruh Aturan Bisnis R1 - R10 teruji dan bekerja dengan pesan kesalahan spesifik saat diuji di browser.

---

## FASE 4: Otorisasi & Scoping Data (Policies & Multi-Role)
- **Deliverables**:
  - Policy classes di `app/Policies/` (`KrsPolicy`, `NilaiPolicy`, `PembayaranPolicy`, `KelasPolicy`, `MahasiswaPolicy`).
  - Query scoping pada Filament Resources (`getEloquentQuery()`).
  - Implementasi Panel Access (`canAccessPanel()`) dan Navigation Visibility (`visible()`).
  - Audit logging minimal (pencatatan `disetujui_oleh` dan `updated_by`).
- **Definition of Done (DoD)**: Mahasiswa hanya melihat data miliknya; Dosen hanya melihat kelas ampuannya; Kaprodi hanya melihat prodi miliknya; Keuangan hanya melihat pembayaran.

---

## FASE 5: Rekapitulasi Akademik, Laporan & Dashboard Widgets
- **Deliverables**:
  - Dashboard Stats Overview Widgets per role (Mahasiswa aktif, KRS pending, tunggakan UKT).
  - Halaman / Action Rekap Akademik:
    - Cetak/Tampilan Kartu Rencana Studi (KRS).
    - Cetak/Tampilan Kartu Hasil Studi (KHS).
    - Cetak/Tampilan Transkrip Akademik Kumulatif.
- **Definition of Done (DoD)**: Dashboard menampilkan summary ringkas yang akurat per role, dan rekap KHS/KRS/Transkrip dapat dilihat secara bersih.

---

## FASE 6: Portal Mahasiswa / Dosen Mandiri (Out of Scope MVP / Fase Lanjut)
- **Deliverables**:
  - Halaman Frontend berbasis Blade + Livewire kustom untuk Mahasiswa & Dosen jika dibutuhkan di masa mendatang.
- **Definition of Done (DoD)**: Dikerjakan hanya jika MVP (Fase 1-5) sudah selesai dan disetujui.
