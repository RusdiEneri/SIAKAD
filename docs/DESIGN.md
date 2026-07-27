# UI / UX Design & Panel Outline Document - SIAKAD

Dokumen ini menjelaskan struktur antarmuka, tata letak menu, alur kerja (workflow), serta pengelompokan navigasi pada Panel Admin Filament SIAKAD.

---

## 1. Tata Letak Navigasi Panel (Filament Navigation Grouping)

Navigasi di dalam Filament Admin Panel dikelompokkan ke dalam kelompok menu berikut, di mana keterlihatan menu dikontrol via method `.visible()` dan Policy berdasarkan Role pengguna:

```
├── Dashboard
├── Master Data
│   ├── Program Studi (Admin)
│   ├── Dosen (Admin)
│   ├── Mahasiswa (Admin, Kaprodi)
│   └── Mata Kuliah & Prasyarat (Admin, Kaprodi)
├── Akademik
│   ├── Semester (Admin)
│   └── Kelas & Jadwal (Admin, Kaprodi, Dosen)
├── Kartu Rencana Studi (KRS)
│   ├── Pengajuan KRS Saya (Mahasiswa)
│   └── Verifikasi & Persetujuan KRS (Kaprodi, Admin)
├── Transkrip & Nilai
│   ├── Input Nilai Kelas Ampuan (Dosen)
│   └── Rekap KHS / Transkrip (Admin, Kaprodi, Mahasiswa)
├── Keuangan & UKT
│   └── Data Pembayaran UKT (Keuangan, Admin, Mahasiswa - view only)
└── Pengaturan & Akses
    ├── Kelola User (Admin)
    └── Kelola Role (Admin)
```

---

## 2. Visibilitas Menu Per Role Matrix

| Menu / Resource | Admin | Kaprodi | Dosen | Mahasiswa | Keuangan |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Dashboard Widgets** | All Stats | Prodi Stats | Kelas Stats | Personal Stats | Keuangan Stats |
| **Prodi Resource** | Full | Read-only | - | - | - |
| **Dosen Resource** | Full | Read-only | Profile | - | - |
| **Mahasiswa Resource** | Full | Scoped Prodi | - | Profile | - |
| **Mata Kuliah Resource** | Full | Scoped Prodi | Read-only | Read-only | - |
| **Semester Resource** | Full | Read-only | Read-only | Read-only | Read-only |
| **Kelas & Jadwal Resource** | Full | Scoped Prodi | Scoped Kelas | Read-only | - |
| **KRS Pengajuan** | Full | - | - | Own Data | - |
| **KRS Approval Action** | Full | Scoped Prodi | - | - | - |
| **Input Nilai Resource** | Full | Scoped Prodi | Scoped Kelas | - | - |
| **Pembayaran UKT Resource** | Full | - | - | Own Status | Full |
| **User & Role Resource** | Full | - | - | - | - |

---

## 3. Workflow & UX Interaksi Utama

### 3.1 Alur Pengambilan KRS oleh Mahasiswa
```
[Buka Form KRS] ──> [Sistem Tampilkan Batas Max SKS (R1) & Status UKT (R6)]
       │
       ▼
[Pilih Kelas Perkulihan]
       │
       ├─► [Sistem Cek Keaktifan Mahasiswa (R8)]
       ├─► [Sistem Cek Matkul Lulus / Duplikat (R7)]
       ├─► [Sistem Cek Kelulusan Prasyarat (R2)]
       ├─► [Sistem Cek Kesesuaian Semester (R5)]
       ├─► [Sistem Cek Kapasitas Kelas (R4)]
       └─► [Sistem Cek Bentrok Jadwal (R3)]
       │
       ├─── (Validasi Gagal) ──► [Tampilkan Warning Notification Spesifik]
       │
       ▼ (Validasi Lolos)
[Tambah ke List Draft KRS] ──> [Hitung Total SKS] ──> [Klik "Ajukan KRS"]
       │
       ▼
[Status KRS: MENUNGGU APPROVAL]
```

---

### 3.2 Alur Persetujuan (Approval) KRS oleh Kaprodi / Admin
```
[Kaprodi Buka List KRS Status: MENUNGGU] ──> [Pilih Mahasiswa]
       │
       ▼
[Review Rincian Kelas & Total SKS]
       │
       ▼
[Klik Tombol "Approve KRS"]
       │
       ▼
[Sistem Cek Gate Pembayaran UKT (R6)]
       │
       ├─── (UKT Belum Lunas) ──► [Notification Error: Pembayaran UKT Belum Lunas!]
       │
       ▼ (UKT Lunas)
[Status KRS Berubah: DISETUJUI] ──► [Record disetujui_oleh & Timestamp]
```

---

### 3.3 Alur Input Nilai oleh Dosen Ampu
```
[Dosen Buka Menu "Input Nilai Kelas Ampuan"] ──> [Pilih Kelas Ampuan]
       │
       ▼
[Tampilkan Tabel Mahasiswa Peserta Kelas (dari krs_details status=diambil)]
       │
       ▼
[Dosen Input Nilai Huruf (mis. A, B+, C)]
       │
       ▼
[System Realtime Auto-Calculate Bobot & Status Lulus (R9)]
       │
       ▼
[Klik "Simpan Nilai"] ──> [Database Insert/Update `nilais` dalam DB::transaction]
```

---

### 3.4 Alur Konfirmasi Pembayaran UKT oleh Keuangan
```
[Staf Keuangan Buka Menu "Data Pembayaran UKT"] ──> [Pilih Tagihan Mahasiswa & Semester]
       │
       ▼
[Input Nominal Bayar & Nomor Referensi]
       │
       ▼
[Ubah Status Pembayaran: LUNAS] ──> [Simpan Record]
       │
       ▼
[Gate UKT (R6) Terbuka Otomatis untuk Approval KRS Mahasiswa Tersebut]
```

---

## 4. Standar Penyampaian Pesan Umpan Balik (Error Presentation)

Pesan kesalahan validasi aturan bisnis tidak boleh bersifat generik. Pesan harus **ramah, spesifik, dan menyebutkan secara lugas alasan serta aturan mana yang dilanggar**.

- **Contoh Salah**: *"Data invalid."* atau *"Gagal mengambil kelas."*
- **Contoh Benar (Sesuai Standar SIAKAD)**:
  - *Bentrok Jadwal (R3)*: `"Gagal menambah kelas: Jadwal Pemrograman Web (Senin, 08:00 - 10:30) bentrok dengan kelas Kalkulus I (Senin, 09:00 - 11:30) yang sudah ada di KRS Anda."`
  - *Prasyarat (R2)*: `"Gagal mengambil Struktur Data: Anda belum lulus mata kuliah prasyarat 'Algoritma & Pemrograman 1'."`
  - *Batas SKS (R1)*: `"Pengajuan KRS ditolak: Total SKS yang Anda pilih (23 SKS) melebihi jatah maksimum SKS Anda (21 SKS) berdasarkan IPS Semester lalu (2.85)."`
  - *UKT Gate (R6)*: `"Approval gagal: Mahasiswa NIM 21001001 belum melunasi Pembayaran UKT untuk Semester 20251."`
