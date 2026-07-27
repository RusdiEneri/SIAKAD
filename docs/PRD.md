# Product Requirement Document (PRD) - SIAKAD (Sistem Informasi Akademik)

## 1. Tujuan & Latar Belakang
SIAKAD (Sistem Informasi Akademik) dirancang untuk mengelola seluruh siklus administratif dan akademik perguruan tinggi secara terintegrasi, transparan, dan akurat. Sistem ini menjamin penegakan aturan akademik (business rules) secara ketat pada setiap transaksi seperti penyusunan KRS, verifikasi prasyarat, batas SKS berbasis IPK, pembatasan bentrok jadwal, serta pembatasan kapasitas kelas dan status pembayaran UKT.

## 2. Ruang Lingkup (Scope)

### 2.1 Ruang Lingkup MVP (Fase Sekarang)
1. **Manajemen Master Data**: User, Role, Prodi, Dosen, Mahasiswa, Mata Kuliah, Prasyarat MK, Semester, Kelas, dan Jadwal.
2. **Proses KRS (Kartu Rencana Studi)**:
   - Pengambilan mata kuliah oleh Mahasiswa.
   - Penegakan Aturan R1 - R8 (Batas SKS, Prasyarat, Bentrok Jadwal, Kapasitas Kelas, Kesesuaian Semester Penawaran, Gate UKT, Anti-Duplikasi/Anti-Mengulang MK Lulus, Status Aktif Mahasiswa).
   - Approval alur KRS oleh Dosen Wali / Kaprodi / Admin.
3. **Manajemen Nilai & Akademik**:
   - Input nilai huruf oleh Dosen Ampu.
   - Perhitungan otomatis Bobot, IPS, dan IPK via `IpkCalculator` service terpusat.
   - Rekap nilai (KHS & Transkrip Akademik).
4. **Manajemen Keuangan / UKT Gate**:
   - Pencatatan & Konfirmasi Pembayaran UKT semesteran.
   - Penguncian approval KRS berdasarkan status lunas UKT (configurable).
5. **Multi-Role & Authorization (Tanpa Package Pihak Ketiga)**:
   - Tabel `roles` + pivot `role_user`.
   - Dynamic role scoping pada Filament Admin Panel.

### 2.2 Di Luar Scope MVP (Fase Lanjut / Out of Scope)
- Portal publik/mahasiswa mandiri berbasis Blade + Livewire kustom (Panel Admin Filament melayani semua role di MVP).
- Gateway pembayaran otomatis (Midtrans/Xendit).
- Notifikasi WhatsApp / Email otomatis.
- Cetak Laporan PDF lanjutan / Watermarking ijazah.
- Activity Logs / Audit Trail komprehensif.

---

## 3. Aktor & Matriks Hak Akses (RBAC)

| Peran (Role) | Hak Akses Utama & Scoping |
| :--- | :--- |
| **Admin** | Akses penuh ke seluruh fitur, master data, konfigurasi sistem, override approval KRS, dan manajemen user. |
| **Kaprodi** | Mengelola data di Prodi miliknya, menyetujui (approve/reject) KRS mahasiswa prodi, serta melihat rekapitulasi akademik prodi. |
| **Dosen** | Melihat daftar kelas ampuan, melihat mahasiswa peserta kelas, menginput/mengubah nilai mahasiswa pada kelas yang diampu. Jika bertindak sebagai Dosen Wali, dapat melihat KRS anak walinya. |
| **Mahasiswa** | Mengakses data personal, menyusun draft KRS semester aktif, melihat KHS, IPK/IPS, jadwal perkuliahan, dan status tagihan UKT. |
| **Keuangan** | Mengelola data pembayaran UKT mahasiswa, melakukan konfirmasi pembayaran, dan memantau rekap tunggakan. |

---

## 4. User Stories & Fitur Utama

### 4.1 Mahasiswa
- **US-MHS-01**: Sebagai mahasiswa aktif, saya ingin memilih kelas perkuliahan pada semester aktif agar saya dapat menyusun KRS.
- **US-MHS-02**: Sebagai mahasiswa, saya ingin sistem langsung memblokir jika saya mengambil SKS melebihi hak SKS saya, bentrok jadwal, atau belum memenuhi prasyarat.
- **US-MHS-03**: Sebagai mahasiswa, saya ingin melihat rincian KHS dan IPK kumulatif untuk memantau kemajuan akademik.

### 4.2 Dosen
- **US-DOS-01**: Sebagai dosen pengampu, saya ingin melihat daftar kelas dan peserta kelas yang saya ampu.
- **US-DOS-02**: Sebagai dosen pengampu, saya ingin memasukkan nilai huruf untuk peserta kelas saya.

### 4.3 Kaprodi
- **US-KAP-01**: Sebagai kaprodi, saya ingin mendownload/memeriksa KRS mahasiswa prodi saya dan melakukan persetujuan (approve/reject).

### 4.4 Keuangan
- **US-KEU-01**: Sebagai staf keuangan, saya ingin mencatat dan mengonfirmasi pembayaran UKT mahasiswa agar status UKT menjadi lunas.

### 4.5 Admin
- **US-ADM-01**: Sebagai admin, saya ingin mengelola master data prodi, dosen, mahasiswa, mata kuliah, kelas, dan user role.

---

## 5. Non-Functional Requirements (NFR)
1. **Data Integrity & Consistency**: Semua transaksi mutasi KRS dan Nilai dibungkus dalam Database Transaction. Soft delete diterapkan pada tabel transaksi (`krs`, `krs_details`, `nilais`, `pembayarans`).
2. **Security & RBAC**: Penegakan otorisasi di tingkat Service Layer dan Policy Laravel, tidak hanya menyembunyikan element UI.
3. **Timezone**: Semua datetime disimpan dan ditampilkan dalam `Asia/Jakarta` (WIB).
4. **Performance**: Bebas dari masalah N+1 query dengan menerapkan eager loading pada eager relations (misal: `with(['details.kelas.mataKuliah'])`).
