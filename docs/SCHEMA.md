# Data Dictionary & Schema Specification - SIAKAD

Dokumen ini berisi spesifikasi teknis rinci untuk seluruh tabel database di SIAKAD.

---

## 1. Tabel: `users`
Menyimpan data identitas autentikasi semua pengguna sistem.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `name` | `string(255)` | No | No | - | - | - | No |
| `email` | `string(255)` | No | Yes | - | UNIQUE (`email`) | - | No |
| `password` | `string(255)` | No | No | - | - | - | No |
| `is_active` | `boolean` | No | No | - | INDEX (`is_active`)| Default: `true` | No |
| `remember_token`| `string(100)` | Yes | No | - | - | - | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 2. Tabel: `roles`
Menyimpan definisi peran (role) aplikasi tanpa package pihak ketiga.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `name` | `string(50)` | No | Yes | - | UNIQUE (`name`) | - | No |
| `guard` | `string(50)` | No | No | - | - | Default: `web` | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 3. Tabel Pivot: `role_user`
Relasi many-to-many antara pengguna dan peran.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `user_id` | `bigint UNSIGNED` | No | No | `users(id)` ON DELETE CASCADE | Primary Composite | - | No |
| `role_id` | `bigint UNSIGNED` | No | No | `roles(id)` ON DELETE CASCADE | Primary Composite | - | No |

---

## 4. Tabel: `prodis`
Menyimpan data Program Studi.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `kode` | `string(20)` | No | Yes | - | UNIQUE (`kode`) | - | No |
| `nama` | `string(100)` | No | No | - | - | - | No |
| `jenjang` | `string(10)` | No | No | - | INDEX (`jenjang`) | Enum: `S1`, `D3`, `D4` | No |
| `fakultas` | `string(100)` | No | No | - | - | - | No |
| `is_active` | `boolean` | No | No | - | INDEX (`is_active`)| Default: `true` | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 5. Tabel: `dosens`
Menyimpan profil dosen yang terhubung ke `users`.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `user_id` | `bigint UNSIGNED` | No | Yes | `users(id)` ON DELETE CASCADE | UNIQUE (`user_id`) | - | No |
| `nidn` | `string(30)` | Yes | Yes | - | UNIQUE (`nidn`) | - | No |
| `prodi_id` | `bigint UNSIGNED` | No | No | `prodis(id)` ON DELETE RESTRICT | INDEX (`prodi_id`) | - | No |
| `is_active` | `boolean` | No | No | - | INDEX (`is_active`)| Default: `true` | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 6. Tabel: `mahasiswas`
Menyimpan profil mahasiswa yang terhubung ke `users`, `prodis`, dan `dosens` (dosen wali).

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `user_id` | `bigint UNSIGNED` | No | Yes | `users(id)` ON DELETE CASCADE | UNIQUE (`user_id`) | - | No |
| `nim` | `string(30)` | No | Yes | - | UNIQUE (`nim`) | - | No |
| `prodi_id` | `bigint UNSIGNED` | No | No | `prodis(id)` ON DELETE RESTRICT | INDEX (`prodi_id`) | - | No |
| `angkatan` | `integer` | No | No | - | INDEX (`angkatan`) | - | No |
| `dosen_wali_id` | `bigint UNSIGNED` | Yes | No | `dosens(id)` ON DELETE SET NULL | INDEX (`dosen_wali_id`)| - | No |
| `status` | `string(20)` | No | No | - | INDEX (`status`) | Enum: `aktif`, `cuti`, `lulus`, `do`, `nonaktif` (Default: `aktif`) | No |
| `tanggal_masuk` | `date` | No | No | - | - | - | No |
| `tanggal_lulus` | `date` | Yes | No | - | - | - | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 7. Tabel: `mata_kuliahs`
Menyimpan data katalog mata kuliah per prodi.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `kode` | `string(20)` | No | Yes | - | UNIQUE (`kode`) | - | No |
| `nama` | `string(150)` | No | No | - | - | - | No |
| `sks` | `unsignedTinyInteger` | No | No | - | - | Min: 1, Max: 6 | No |
| `semester_penawaran` | `string(20)` | No | No | - | INDEX (`semester_penawaran`) | Enum: `ganjil`, `genap`, `semua` | No |
| `prodi_id` | `bigint UNSIGNED` | No | No | `prodis(id)` ON DELETE RESTRICT | INDEX (`prodi_id`) | - | No |
| `is_active` | `boolean` | No | No | - | INDEX (`is_active`)| Default: `true` | No |
| `deskripsi` | `text` | Yes | No | - | - | - | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 8. Tabel Pivot: `mata_kuliah_prasyarat`
Relasi self-many-to-many untuk mendefinisikan prasyarat mata kuliah.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `mata_kuliah_id` | `bigint UNSIGNED` | No | No | `mata_kuliahs(id)` ON DELETE CASCADE | Primary Composite | - | No |
| `prasyarat_id` | `bigint UNSIGNED` | No | No | `mata_kuliahs(id)` ON DELETE CASCADE | Primary Composite | - | No |

---

## 9. Tabel: `semesters`
Menyimpan data periode semester akademik.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `kode` | `string(10)` | No | Yes | - | UNIQUE (`kode`) | e.g. "20251" | No |
| `nama` | `string(50)` | No | No | - | - | e.g. "2025/2026 Ganjil" | No |
| `tahun` | `integer` | No | No | - | INDEX (`tahun`) | e.g. 2025 | No |
| `jenis` | `string(20)` | No | No | - | INDEX (`jenis`) | Enum: `ganjil`, `genap`, `pendek` | No |
| `tanggal_mulai` | `date` | No | No | - | - | - | No |
| `tanggal_akhir` | `date` | No | No | - | - | - | No |
| `is_active` | `boolean` | No | No | - | INDEX (`is_active`)| Default: `false` | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 10. Tabel: `kelas`
Menyimpan seksi kelas perkuliahan yang ditawarkan pada semester tertentu.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `kode` | `string(30)` | No | Yes | - | UNIQUE (`kode`) | e.g. "TIF-20251-A" | No |
| `mata_kuliah_id` | `bigint UNSIGNED` | No | No | `mata_kuliahs(id)` ON DELETE RESTRICT | INDEX (`mata_kuliah_id`) | - | No |
| `semester_id` | `bigint UNSIGNED` | No | No | `semesters(id)` ON DELETE RESTRICT | INDEX (`semester_id`) | - | No |
| `dosen_id` | `bigint UNSIGNED` | No | No | `dosens(id)` ON DELETE RESTRICT | INDEX (`dosen_id`) | - | No |
| `kapasitas` | `integer` | No | No | - | - | e.g. 40 | No |
| `ruang` | `string(50)` | No | No | - | - | e.g. "Lab Komputer 1" | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 11. Tabel: `jadwals`
Menyimpan slot waktu dan hari pelaksanaan kuliah per kelas.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | No |
| `kelas_id` | `bigint UNSIGNED` | No | No | `kelas(id)` ON DELETE CASCADE | INDEX (`kelas_id`) | - | No |
| `hari` | `string(15)` | No | No | - | INDEX (`hari`) | Enum: `senin`, `selasa`, `rabu`, `kamis`, `jumat`, `sabtu`, `minggu` | No |
| `jam_mulai` | `time` | No | No | - | - | Format: `HH:mm:ss` | No |
| `jam_selesai` | `time` | No | No | - | - | Format: `HH:mm:ss` | No |
| `created_at` | `timestamp` | Yes | No | - | - | - | No |
| `updated_at` | `timestamp` | Yes | No | - | - | - | No |

---

## 12. Tabel Transaksi: `krs`
Menyimpan pengajuan Kartu Rencana Studi (KRS) Mahasiswa per semester.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | **Yes (`deleted_at`)** |
| `mahasiswa_id` | `bigint UNSIGNED` | No | No | `mahasiswas(id)` ON DELETE RESTRICT | INDEX (`mahasiswa_id`) | - | Yes |
| `semester_id` | `bigint UNSIGNED` | No | No | `semesters(id)` ON DELETE RESTRICT | INDEX (`semester_id`) | - | Yes |
| `total_sks` | `integer` | No | No | - | - | Default: `0` | Yes |
| `status` | `string(20)` | No | No | - | INDEX (`status`) | Enum: `draft`, `menunggu`, `disetujui`, `ditolak`, `batal` (Default: `draft`) | Yes |
| `disetujui_oleh` | `bigint UNSIGNED` | Yes | No | `users(id)` ON DELETE SET NULL | INDEX (`disetujui_oleh`)| - | Yes |
| `catatan` | `text` | Yes | No | - | - | - | Yes |
| `created_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `updated_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `deleted_at` | `timestamp` | Yes | No | - | INDEX (`deleted_at`)| Soft delete | Yes |

*Unique Composite Constraint*: `(mahasiswa_id, semester_id)` di mana `deleted_at IS NULL`.

---

## 13. Tabel Transaksi: `krs_details`
Menyimpan item rincian kelas yang diambil di dalam KRS.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | **Yes (`deleted_at`)** |
| `krs_id` | `bigint UNSIGNED` | No | No | `krs(id)` ON DELETE CASCADE | INDEX (`krs_id`) | - | Yes |
| `kelas_id` | `bigint UNSIGNED` | No | No | `kelas(id)` ON DELETE RESTRICT | INDEX (`kelas_id`) | - | Yes |
| `status` | `string(20)` | No | No | - | INDEX (`status`) | Enum: `diambil`, `batal` (Default: `diambil`) | Yes |
| `created_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `updated_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `deleted_at` | `timestamp` | Yes | No | - | INDEX (`deleted_at`)| Soft delete | Yes |

*Unique Composite Constraint*: `(krs_id, kelas_id)` di mana `deleted_at IS NULL`.

---

## 14. Tabel Transaksi: `nilais`
Menyimpan nilai mahasiswa per rincian kelas (`krs_details`).

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | **Yes (`deleted_at`)** |
| `krs_detail_id` | `bigint UNSIGNED` | No | Yes | `krs_details(id)` ON DELETE RESTRICT | UNIQUE (`krs_detail_id`) | - | Yes |
| `nilai_huruf` | `string(5)` | No | No | - | INDEX (`nilai_huruf`) | Enum: `A`, `A-`, `B+`, `B`, `B-`, `C+`, `C`, `D`, `E` | Yes |
| `bobot` | `decimal(3,2)` | No | No | - | - | `4.00`, `3.70`, `3.30`, dll. | Yes |
| `sks` | `unsignedTinyInteger` | No | No | - | - | Denormalisasi dari `mata_kuliahs.sks` | Yes |
| `lulus` | `boolean` | No | No | - | INDEX (`lulus`) | `true` jika bobot >= 2.00 (C ke atas) | Yes |
| `tahun_akademik`| `string(20)` | No | No | - | - | e.g. "2025/2026" | Yes |
| `created_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `updated_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `deleted_at` | `timestamp` | Yes | No | - | INDEX (`deleted_at`)| Soft delete | Yes |

---

## 15. Tabel Transaksi: `pembayarans`
Menyimpan pencatatan tagihan dan pembayaran UKT mahasiswa per semester.

| Nama Kolom | Tipe Data | Nullable | Unique | FK / Relasi | Indeks | Enum / Default | Soft Delete |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| `id` | `bigint UNSIGNED` | No | Auto-inc | - | Primary Key | - | **Yes (`deleted_at`)** |
| `mahasiswa_id` | `bigint UNSIGNED` | No | No | `mahasiswas(id)` ON DELETE RESTRICT | INDEX (`mahasiswa_id`) | - | Yes |
| `semester_id` | `bigint UNSIGNED` | No | No | `semesters(id)` ON DELETE RESTRICT | INDEX (`semester_id`) | - | Yes |
| `jenis` | `string(20)` | No | No | - | INDEX (`jenis`) | Enum: `ukt`, `lainnya` (Default: `ukt`) | Yes |
| `nominal` | `decimal(12,2)` | No | No | - | - | Nominal rupiah | Yes |
| `status` | `string(20)` | No | No | - | INDEX (`status`) | Enum: `belum`, `sebagian`, `lunas` (Default: `belum`) | Yes |
| `tanggal_bayar` | `date` | Yes | No | - | - | - | Yes |
| `referensi` | `string(100)` | Yes | No | - | - | Nomor kuitansi / bank ref | Yes |
| `created_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `updated_at` | `timestamp` | Yes | No | - | - | - | Yes |
| `deleted_at` | `timestamp` | Yes | No | - | INDEX (`deleted_at`)| Soft delete | Yes |
