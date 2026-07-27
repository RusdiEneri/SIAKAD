<?php

namespace Database\Seeders;

use App\Enums\HariEnum;
use App\Enums\JenjangEnum;
use App\Enums\PembayaranJenisEnum;
use App\Enums\PembayaranStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\SemesterJenisEnum;
use App\Enums\SemesterPenawaranEnum;
use App\Enums\StatusMahasiswaEnum;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pembayaran;
use App\Models\Prodi;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('name', RoleEnum::ADMIN->value)->first();
        $kaprodiRole = Role::where('name', RoleEnum::KAPRODI->value)->first();
        $dosenRole = Role::where('name', RoleEnum::DOSEN->value)->first();
        $mhsRole = Role::where('name', RoleEnum::MAHASISWA->value)->first();
        $keuanganRole = Role::where('name', RoleEnum::KEUANGAN->value)->first();

        // 2. Admin User
        $adminUser = User::create([
            'name' => 'Administrator SIAKAD',
            'email' => 'admin@siakad.ac.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($adminRole);

        // 3. Staf Keuangan User
        $keuanganUser = User::create([
            'name' => 'Staf Keuangan',
            'email' => 'keuangan@siakad.ac.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $keuanganUser->roles()->attach($keuanganRole);

        // 4. Program Studi
        $prodiTif = Prodi::create([
            'kode' => 'TIF',
            'nama' => 'Teknik Informatika',
            'jenjang' => JenjangEnum::S1,
            'fakultas' => 'Fakultas Ilmu Komputer',
            'is_active' => true,
        ]);

        $prodiSi = Prodi::create([
            'kode' => 'SI',
            'nama' => 'Sistem Informasi',
            'jenjang' => JenjangEnum::S1,
            'fakultas' => 'Fakultas Ilmu Komputer',
            'is_active' => true,
        ]);

        // 5. User Kaprodi & Profile Dosen
        $kaprodiUser = User::create([
            'name' => 'Dr. Kaprodi Informatika, M.Kom',
            'email' => 'kaprodi@siakad.ac.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $kaprodiUser->roles()->attach([$dosenRole->id, $kaprodiRole->id]);

        $dosenKaprodi = Dosen::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '0001018501',
            'prodi_id' => $prodiTif->id,
            'is_active' => true,
        ]);

        // 6. User Dosen Biasa & Profile Dosen
        $dosenUser = User::create([
            'name' => 'Budi Santoso, M.T.',
            'email' => 'dosen@siakad.ac.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $dosenUser->roles()->attach($dosenRole);

        $dosenBiasa = Dosen::create([
            'user_id' => $dosenUser->id,
            'nidn' => '0002028802',
            'prodi_id' => $prodiTif->id,
            'is_active' => true,
        ]);

        // 7. User Mahasiswa & Profile Mahasiswa
        $mhsUser = User::create([
            'name' => 'Ahmad Mahasiswa',
            'email' => 'mahasiswa@siakad.ac.id',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $mhsUser->roles()->attach($mhsRole);

        $mahasiswa = Mahasiswa::create([
            'user_id' => $mhsUser->id,
            'nim' => '22001001',
            'prodi_id' => $prodiTif->id,
            'angkatan' => 2022,
            'dosen_wali_id' => $dosenKaprodi->id,
            'status' => StatusMahasiswaEnum::AKTIF,
            'tanggal_masuk' => '2022-09-01',
        ]);

        // 8. Semester
        $semesterAktif = Semester::create([
            'kode' => '20251',
            'nama' => '2025/2026 Ganjil',
            'tahun' => 2025,
            'jenis' => SemesterJenisEnum::GANJIL,
            'tanggal_mulai' => '2025-09-01',
            'tanggal_akhir' => '2026-01-31',
            'is_active' => true,
        ]);

        // 9. Mata Kuliah
        $mkAlgo1 = MataKuliah::create([
            'kode' => 'TIF101',
            'nama' => 'Algoritma & Pemrograman 1',
            'sks' => 3,
            'semester_penawaran' => SemesterPenawaranEnum::GANJIL,
            'prodi_id' => $prodiTif->id,
            'is_active' => true,
            'deskripsi' => 'Pengenalan konsep logika pemrograman dasar.',
        ]);

        $mkAlgo2 = MataKuliah::create([
            'kode' => 'TIF201',
            'nama' => 'Algoritma & Pemrograman 2',
            'sks' => 3,
            'semester_penawaran' => SemesterPenawaranEnum::GANJIL,
            'prodi_id' => $prodiTif->id,
            'is_active' => true,
            'deskripsi' => 'Pemrograman berorientasi objek dan struktur data dasar.',
        ]);
        // Set Prasyarat: Algo2 butuh Algo1
        $mkAlgo2->prasyarat()->attach($mkAlgo1->id);

        $mkKalkulus = MataKuliah::create([
            'kode' => 'TIF102',
            'nama' => 'Kalkulus I',
            'sks' => 3,
            'semester_penawaran' => SemesterPenawaranEnum::GANJIL,
            'prodi_id' => $prodiTif->id,
            'is_active' => true,
            'deskripsi' => 'Matematika dasar diferensial dan integral.',
        ]);

        $mkWeb = MataKuliah::create([
            'kode' => 'TIF301',
            'nama' => 'Pemrograman Web',
            'sks' => 4,
            'semester_penawaran' => SemesterPenawaranEnum::GANJIL,
            'prodi_id' => $prodiTif->id,
            'is_active' => true,
            'deskripsi' => 'Pengembangan aplikasi web modern.',
        ]);

        // 10. Kelas & Jadwal
        $kelasAlgo1 = Kelas::create([
            'kode' => 'TIF101-20251-A',
            'mata_kuliah_id' => $mkAlgo1->id,
            'semester_id' => $semesterAktif->id,
            'dosen_id' => $dosenBiasa->id,
            'kapasitas' => 30,
            'ruang' => 'Lab Komputer 1',
        ]);
        Jadwal::create([
            'kelas_id' => $kelasAlgo1->id,
            'hari' => HariEnum::SENIN,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:30:00',
        ]);

        $kelasKalkulus = Kelas::create([
            'kode' => 'TIF102-20251-A',
            'mata_kuliah_id' => $mkKalkulus->id,
            'semester_id' => $semesterAktif->id,
            'dosen_id' => $dosenKaprodi->id,
            'kapasitas' => 40,
            'ruang' => 'Ruang Teori 201',
        ]);
        Jadwal::create([
            'kelas_id' => $kelasKalkulus->id,
            'hari' => HariEnum::SELASA,
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '12:30:00',
        ]);

        $kelasWeb = Kelas::create([
            'kode' => 'TIF301-20251-A',
            'mata_kuliah_id' => $mkWeb->id,
            'semester_id' => $semesterAktif->id,
            'dosen_id' => $dosenBiasa->id,
            'kapasitas' => 35,
            'ruang' => 'Lab Web',
        ]);
        Jadwal::create([
            'kelas_id' => $kelasWeb->id,
            'hari' => HariEnum::RABU,
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '16:20:00',
        ]);

        // 11. Pembayaran UKT Lunas
        Pembayaran::create([
            'mahasiswa_id' => $mahasiswa->id,
            'semester_id' => $semesterAktif->id,
            'jenis' => PembayaranJenisEnum::UKT,
            'nominal' => 4500000.00,
            'status' => PembayaranStatusEnum::LUNAS,
            'tanggal_bayar' => '2025-08-15',
            'referensi' => 'BYR-20251-22001001',
        ]);
    }
}
