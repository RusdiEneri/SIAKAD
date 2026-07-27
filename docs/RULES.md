# Aturan Bisnis & Spesifikasi Kasus Uji (Business Rules & Test Specifications) - SIAKAD

Dokumen ini mendefinisikan aturan akademik (R1 - R14) yang bersifat mutlak ("hukum sistem") beserta skenario kasus uji untuk validasi sistem SIAKAD.

---

## R1. Batas Maksimum SKS Berdasarkan IPK/IPS Semester Sebelumnya
- **Deskripsi**: Jumlah SKS maksimum yang boleh diambil mahasiswa pada semester aktif dihitung berdasarkan IPS semester lalu (atau IPK kumulatif jika semester lalu tidak ada). Konfigurasi tier disimpan di `config/siakad.php`.
- **Tier Default Config**:
  - IPK / IPS >= 3.00: Maksimal **24 SKS**
  - 2.50 <= IPK / IPS < 3.00: Maksimal **21 SKS**
  - 2.00 <= IPK / IPS < 2.50: Maksimal **18 SKS**
  - IPK / IPS < 2.00: Maksimal **15 SKS**
  - Mahasiswa Semester 1 (Baru): Default **20 SKS**
- **Test Case R1**:
  - *Input*: Mahasiswa IPS semester lalu 2.75 mencoba mengambil total 23 SKS.
  - *Expected*: Gagal validation error: "Total SKS (23) melebihi batas maksimum SKS Anda (21 SKS) untuk IPS 2.75."

---

## R2. Validasi Kelulusan Mata Kuliah Prasyarat
- **Deskripsi**: Mata kuliah yang memiliki prasyarat hanya boleh diambil jika mahasiswa telah **LULUS** (`lulus = true`) di SELURUH mata kuliah prasyaratnya pada semester-semester sebelumnya. Jika mata kuliah tidak memiliki prasyarat, validasi ini dilewati.
- **Test Case R2**:
  - *Input*: Mahasiswa mengambil "Algoritma & Pemrograman 2" (prasyarat: "Algoritma 1"), namun nilai "Algoritma 1" mahasiswa adalah 'E' (`lulus = false`).
  - *Expected*: Gagal validation error: "Mata kuliah Algoritma & Pemrograman 2 membutuhkan prasyarat Algoritma 1 yang belum Anda lulusi."

---

## R3. Validasi Bentrok Jadwal Perkuliahan
- **Deskripsi**: Mahasiswa tidak boleh mengambil dua kelas yang memiliki jadwal beririsan pada hari yang sama. Dua jadwal `[A_start, A_end]` dan `[B_start, B_end]` dinyatakan bentrok jika: `Hari_A == Hari_B` DAN `(A_start < B_end AND A_end > B_start)`.
- **Test Case R3**:
  - *Input*: Kelas A (Senin 08:00 - 10:30) sudah ada di KRS. Mahasiswa menambah Kelas B (Senin 10:00 - 11:40).
  - *Expected*: Gagal validation error: "Jadwal Kelas B (Senin 10:00-11:40) bentrok dengan kelas yang sudah diambil: Kelas A (Senin 08:00-10:30)."

---

## R4. Pembatasan Kapasitas Kelas
- **Deskripsi**: Jumlah rincian KRS (`krs_details`) yang berstatus `diambil` pada suatu kelas tidak boleh melebihi atribut `kapasitas` dari kelas tersebut.
- **Test Case R4**:
  - *Input*: Kelas Pemrograman Web memiliki `kapasitas = 30` dan sudah diisi 30 mahasiswa. Mahasiswa ke-31 mencoba mengambil kelas tersebut.
  - *Expected*: Gagal validation error: "Kapasitas kelas Pemrograman Web sudah penuh (30/30)."

---

## R5. Kesesuaian Semester Penawaran Mata Kuliah
- **Deskripsi**: Kelas/Mata Kuliah hanya dapat ditawarkan dan diambil jika `semester_penawaran` mata kuliah (`ganjil`/`genap`/`semua`) sesuai dengan `jenis` semester aktif saat ini (`ganjil`/`genap`/`pendek`).
- **Test Case R5**:
  - *Input*: Semester aktif adalah Semester Ganjil 2025/2026. Mahasiswa mengambil kelas "Tugas Akhir" yang `semester_penawaran = genap`.
  - *Expected*: Gagal validation error: "Mata kuliah Tugas Akhir hanya ditawarkan pada semester Genap."

---

## R6. Gate Pembayaran UKT (Konfigurabel)
- **Deskripsi**: KRS mahasiswa hanya dapat di-approve (disetujui oleh Dosen Wali/Kaprodi) jika record pembayaran UKT mahasiswa pada semester terkait berstatus `lunas`. Fitur ini dikontrol oleh flag `config('siakad.ukt_gate_enabled')`.
- **Test Case R6**:
  - *Input*: `ukt_gate_enabled = true`. Staf Kaprodi mencoba approve KRS Mahasiswa A yang pembayaran UKT-nya berstatus `belum` atau `sebagian`.
  - *Expected*: Gagal approval error: "KRS tidak dapat disetujui karena pembayaran UKT semester ini belum Lunas."

---

## R7. Pencegahan Ambil Kelas Duplikat & Mata Kuliah Lulus
- **Deskripsi**:
  1. Mahasiswa tidak boleh mengambil lebih dari 1 kelas untuk mata kuliah yang sama di semester yang sama.
  2. Mahasiswa tidak boleh mengambil mata kuliah yang sudah pernah di-LULUS-kan (`lulus = true`) di semester sebelumnya, kecuali dilakukan melalui alur mengulang MK yang eksplisit (jika diaktifkan di kebijakan).
- **Test Case R7**:
  - *Input*: Mahasiswa sudah lulus MK "Matematika Diskrit" dengan nilai 'B' di semester lalu. Mencoba mengambil kelas "Matematika Diskrit" lagi di semester aktif.
  - *Expected*: Gagal validation error: "Anda sudah lulus mata kuliah Matematika Diskrit dengan nilai B."

---

## R8. Pembatasan Status Keaktifan Mahasiswa
- **Deskripsi**: Hanya mahasiswa dengan status `status = aktif` yang diizinkan untuk membuat, mengedit, atau mengajukan KRS. Mahasiswa berstatus `cuti`, `nonaktif`, `lulus`, atau `do` diblokir total dari penyusunan KRS.
- **Test Case R8**:
  - *Input*: Mahasiswa berstatus `cuti` mencoba membuka form penyusunan KRS.
  - *Expected*: Access denied error: "Status akademik Anda saat ini adalah Cuti. Anda tidak diizinkan menyusun KRS."

---

## R9. Konversi Terpusat Nilai Huruf ke Bobot & Kelulusan
- **Deskripsi**: Mapping Nilai Huruf -> Bobot Kuantitatif -> Status Lulus ditentukan secara terpusat di `config/siakad.php` atau `NilaiHuruf` Enum.
- **Tabel Standar Mapping**:
  - `A`  -> Bobot `4.00`, Lulus `true`
  - `A-` -> Bobot `3.70`, Lulus `true`
  - `B+` -> Bobot `3.30`, Lulus `true`
  - `B`  -> Bobot `3.00`, Lulus `true`
  - `B-` -> Bobot `2.70`, Lulus `true`
  - `C+` -> Bobot `2.30`, Lulus `true`
  - `C`  -> Bobot `2.00`, Lulus `true`
  - `D`  -> Bobot `1.00`, Lulus `false`
  - `E`  -> Bobot `0.00`, Lulus `false`
- **Test Case R9**:
  - *Input*: Dosen memasukkan nilai huruf `B+` untuk mahasiswa di kelas 3 SKS.
  - *Expected*: Record `nilais` otomatis menyimpan `bobot = 3.30`, `sks = 3`, dan `lulus = true`.

---

## R10. Service Terpusat Perhitungan IPS & IPK (`IpkCalculator`)
- **Deskripsi**: Perhitungan IPS (Indeks Prestasi Semester) dan IPK (Indeks Prestasi Kumulatif) WAJIB dilakukan melalui `App\Services\IpkCalculator`. Formula:
  $$\text{IPK} = \frac{\sum (\text{Bobot} \times \text{SKS})}{\sum \text{SKS}}$$
  di mana pembagi adalah total SKS dari nilai yang diambil. Hasil dibulatkan ke 2 desimal (`round(..., 2)`).
- **Test Case R10**:
  - *Input*: Mahasiswa memiliki 2 nilai: (A: 4 SKS, Bobot 4.00) dan (C: 2 SKS, Bobot 2.00).
  - *Expected*: Total Mutu = (4*4) + (2*2) = 20. Total SKS = 6. IPK = 20 / 6 = 3.3333... -> dibulatkan menjadi `3.33`.

---

## R11. Otorisasi Data (Data Scoping & Policy)
- **Deskripsi**:
  - **Mahasiswa**: Hanya dapat membaca dan memutasi KRS & KHS miliknya sendiri (`mahasiswa_id == current_user->mahasiswa->id`).
  - **Dosen**: Hanya dapat melihat dan menginput nilai untuk kelas yang diampu (`dosen_id == current_user->dosen->id`).
  - **Kaprodi**: Hanya melihat dan meng-approve data milik Prodi tempat ia bertugas (`prodi_id == current_user->dosen->prodi_id`).
  - **Keuangan**: Hanya mengelola transaksi pembayaran UKT.
  - **Admin**: Akses tanpa batasan scope.
- **Test Case R11**:
  - *Input*: Dosen B mencoba membuka URL input nilai untuk kelas yang diampu Dosen A.
  - *Expected*: HTTP 403 Forbidden / Policy Denial.

---

## R12. Transaksi dan Soft Delete
- **Deskripsi**: Data transaksi (`krs`, `krs_details`, `nilais`, `pembayarans`) TIDAK BOLEH di-hard delete secara fisik dari database. Semua penghapusan menggunakan Eloquent `SoftDeletes` (`deleted_at`).
- **Test Case R12**:
  - *Input*: Admin menghapus KRS Mahasiswa.
  - *Expected*: Row di database tetap ada, kolom `deleted_at` terisi timestamp, dan record disembunyikan dari query biasa.

---

## R13. Audit Identitas Pembatalan & Persetujuan
- **Deskripsi**: Setiap persetujuan KRS harus mencatat `disetujui_oleh` (ID User peng-approve) dan timestamp update. Setiap modifikasi nilai harus mencatat timestamp dan identitas pengubah.
- **Test Case R13**:
  - *Input*: Kaprodi X meng-approve KRS Mahasiswa Y.
  - *Expected*: Kolom `krs.disetujui_oleh` terisi ID User Kaprodi X dan status berubah menjadi `disetujui`.

---

## R14. Timezone & Handling Tanggal Standard
- **Deskripsi**: Seluruh tanggal dan timestamp diproses dan disimpan menggunakan Timezone `Asia/Jakarta` (WIB). Model Eloquent harus menggunakan date casting (`'tanggal_masuk' => 'date'`).
- **Test Case R14**:
  - *Input*: Sistem mencatat `tanggal_bayar` pembayaran UKT pada jam 23:30 WIB.
  - *Expected*: Tanggal tersimpan tepat sesuai kalender WIB, tidak bergeser hari karena konversi UTC.
