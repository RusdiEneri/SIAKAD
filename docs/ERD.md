# Entity Relationship Diagram (ERD) - SIAKAD

Diagram ERD berikut menggambarkan struktur seluruh entitas, atribut utama, serta hubungan antar entitas (dengan kardinalitas) untuk Sistem Informasi Akademik (SIAKAD).

```mermaid
erDiagram
    USERS ||--o{ ROLE_USER : "has"
    ROLES ||--o{ ROLE_USER : "assigned_to"
    
    USERS ||--o| DOSENS : "user_account"
    USERS ||--o| MAHASISWAS : "user_account"
    
    PRODIS ||--o{ DOSENS : "has_lecturers"
    PRODIS ||--o{ MAHASISWAS : "has_students"
    PRODIS ||--o{ MATA_KULIAHS : "owns"
    
    DOSENS ||--o{ MAHASISWAS : "dosen_wali"
    DOSENS ||--o{ KELAS : "teaches"
    
    MATA_KULIAHS ||--o{ MATA_KULIAH_PRASYARAT : "main_course"
    MATA_KULIAHS ||--o{ MATA_KULIAH_PRASYARAT : "prerequisite_course"
    MATA_KULIAHS ||--o{ KELAS : "instantiated_in"
    
    SEMESTERS ||--o{ KELAS : "active_in"
    SEMESTERS ||--o{ KRS : "period"
    SEMESTERS ||--o{ PEMBAYARANS : "billing_period"
    
    KELAS ||--o{ JADWALS : "scheduled_at"
    KELAS ||--o{ KRS_DETAILS : "enrolled_in"
    
    MAHASISWAS ||--o{ KRS : "submits"
    MAHASISWAS ||--o{ PEMBAYARANS : "makes"
    
    USERS ||--o{ KRS : "approves"
    
    KRS ||--o{ KRS_DETAILS : "contains"
    KRS_DETAILS ||--o| NILAIS : "graded_by"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    ROLES {
        bigint id PK
        string name UK
        string guard
        datetime created_at
        datetime updated_at
    }

    ROLE_USER {
        bigint user_id FK
        bigint role_id FK
    }

    PRODIS {
        bigint id PK
        string kode UK
        string nama
        enum jenjang "S1, D3, D4"
        string fakultas
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    DOSENS {
        bigint id PK
        bigint user_id FK
        string nidn UK "nullable"
        bigint prodi_id FK
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    MAHASISWAS {
        bigint id PK
        bigint user_id FK
        string nim UK
        bigint prodi_id FK
        integer angkatan
        bigint dosen_wali_id FK "nullable"
        enum status "aktif, cuti, lulus, do, nonaktif"
        date tanggal_masuk
        date tanggal_lulus "nullable"
        datetime created_at
        datetime updated_at
    }

    MATA_KULIAHS {
        bigint id PK
        string kode UK
        string nama
        tinyint sks
        enum semester_penawaran "ganjil, genap, semua"
        bigint prodi_id FK
        boolean is_active
        text deskripsi "nullable"
        datetime created_at
        datetime updated_at
    }

    MATA_KULIAH_PRASYARAT {
        bigint mata_kuliah_id FK
        bigint prasyarat_id FK
    }

    SEMESTERS {
        bigint id PK
        string kode UK "contoh: 20251"
        string nama
        integer tahun
        enum jenis "ganjil, genap, pendek"
        date tanggal_mulai
        date tanggal_akhir
        boolean is_active
        datetime created_at
        datetime updated_at
    }

    KELAS {
        bigint id PK
        string kode UK
        bigint mata_kuliah_id FK
        bigint semester_id FK
        bigint dosen_id FK
        integer kapasitas
        string ruang
        datetime created_at
        datetime updated_at
    }

    JADWALS {
        bigint id PK
        bigint kelas_id FK
        enum hari "senin, selasa, rabu, kamis, jumat, sabtu, minggu"
        time jam_mulai
        time jam_selesai
        datetime created_at
        datetime updated_at
    }

    KRS {
        bigint id PK
        bigint mahasiswa_id FK
        bigint semester_id FK
        integer total_sks
        enum status "draft, menunggu, disetujui, ditolak, batal"
        bigint disetujui_oleh FK "nullable"
        text catatan "nullable"
        datetime deleted_at "soft delete"
        datetime created_at
        datetime updated_at
    }

    KRS_DETAILS {
        bigint id PK
        bigint krs_id FK
        bigint kelas_id FK
        enum status "diambil, batal"
        datetime deleted_at "soft delete"
        datetime created_at
        datetime updated_at
    }

    NILAIS {
        bigint id PK
        bigint krs_detail_id FK UK
        enum nilai_huruf "A, A-, B+, B, B-, C+, C, D, E"
        decimal bobot "3,2"
        tinyint sks
        boolean lulus
        string tahun_akademik
        datetime deleted_at "soft delete"
        datetime created_at
        datetime updated_at
    }

    PEMBAYARANS {
        bigint id PK
        bigint mahasiswa_id FK
        bigint semester_id FK
        enum jenis "ukt, lainnya"
        decimal nominal "12,2"
        enum status "belum, sebagian, lunas"
        date tanggal_bayar "nullable"
        string referensi "nullable"
        datetime deleted_at "soft delete"
        datetime created_at
        datetime updated_at
    }
```
