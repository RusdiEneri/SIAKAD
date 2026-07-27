<?php

namespace Tests\Unit;

use App\Enums\HariEnum;
use App\Enums\KrsDetailStatusEnum;
use App\Enums\KrsStatusEnum;
use App\Enums\NilaiHurufEnum;
use App\Enums\PembayaranStatusEnum;
use App\Enums\SemesterPenawaranEnum;
use App\Enums\StatusMahasiswaEnum;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\Pembayaran;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\IpkCalculator;
use App\Services\KrsApprovalService;
use App\Services\ScheduleConflictChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SiakadServicesTest extends TestCase
{
    use RefreshDatabase;

    protected EnrollmentService $enrollmentService;

    protected KrsApprovalService $approvalService;

    protected IpkCalculator $ipkCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->ipkCalculator = new IpkCalculator();
        $this->enrollmentService = new EnrollmentService(
            $this->ipkCalculator,
            new ScheduleConflictChecker()
        );
        $this->approvalService = new KrsApprovalService();
    }

    public function test_r8_student_status_must_be_active(): void
    {
        $mhs = Mahasiswa::first();
        $mhs->update(['status' => StatusMahasiswaEnum::CUTI]);

        $semester = Semester::first();
        $kelas = Kelas::first();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Status akademik Anda saat ini adalah Cuti');

        $this->enrollmentService->addClassToKrs($mhs, $semester, $kelas);
    }

    public function test_r5_semester_offering_mismatch_fails(): void
    {
        $mhs = Mahasiswa::first();
        $semester = Semester::first(); // Ganjil

        $prodi = Prodi::first();
        $mkGenap = MataKuliah::create([
            'kode' => 'MK-GENAP',
            'nama' => 'Mata Kuliah Genap',
            'sks' => 3,
            'semester_penawaran' => SemesterPenawaranEnum::GENAP,
            'prodi_id' => $prodi->id,
            'is_active' => true,
        ]);

        $kelasGenap = Kelas::create([
            'kode' => 'MK-GENAP-A',
            'mata_kuliah_id' => $mkGenap->id,
            'semester_id' => $semester->id,
            'dosen_id' => Dosen::first()->id,
            'kapasitas' => 30,
            'ruang' => 'Ruang 101',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('hanya ditawarkan pada semester Genap');

        $this->enrollmentService->addClassToKrs($mhs, $semester, $kelasGenap);
    }

    public function test_r2_prerequisite_must_be_passed(): void
    {
        $mhs = Mahasiswa::first();
        $semester = Semester::first();
        $dosen = Dosen::first();

        $mkAlgo2 = MataKuliah::where('kode', 'TIF201')->first();
        $kelasAlgo2 = Kelas::create([
            'kode' => 'TIF201-20251-A',
            'mata_kuliah_id' => $mkAlgo2->id,
            'semester_id' => $semester->id,
            'dosen_id' => $dosen->id,
            'kapasitas' => 30,
            'ruang' => 'Lab Komputer 2',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('membutuhkan prasyarat: Algoritma & Pemrograman 1 (TIF101)');

        $this->enrollmentService->addClassToKrs($mhs, $semester, $kelasAlgo2);
    }

    public function test_r3_schedule_conflict_fails(): void
    {
        $mhs = Mahasiswa::first();
        $semester = Semester::first();
        $dosen = Dosen::first();
        $prodi = Prodi::first();

        // MK 1 (Senin 08:00 - 10:00)
        $mk1 = MataKuliah::create(['kode' => 'MK1', 'nama' => 'Matkul 1', 'sks' => 3, 'semester_penawaran' => 'ganjil', 'prodi_id' => $prodi->id]);
        $kelas1 = Kelas::create(['kode' => 'K1', 'mata_kuliah_id' => $mk1->id, 'semester_id' => $semester->id, 'dosen_id' => $dosen->id, 'kapasitas' => 30, 'ruang' => 'R1']);
        Jadwal::create(['kelas_id' => $kelas1->id, 'hari' => HariEnum::SENIN, 'jam_mulai' => '08:00:00', 'jam_selesai' => '10:00:00']);

        // MK 2 (Senin 09:00 - 11:00) -> Bentrok!
        $mk2 = MataKuliah::create(['kode' => 'MK2', 'nama' => 'Matkul 2', 'sks' => 3, 'semester_penawaran' => 'ganjil', 'prodi_id' => $prodi->id]);
        $kelas2 = Kelas::create(['kode' => 'K2', 'mata_kuliah_id' => $mk2->id, 'semester_id' => $semester->id, 'dosen_id' => $dosen->id, 'kapasitas' => 30, 'ruang' => 'R2']);
        Jadwal::create(['kelas_id' => $kelas2->id, 'hari' => HariEnum::SENIN, 'jam_mulai' => '09:00:00', 'jam_selesai' => '11:00:00']);

        // Tambah kelas 1
        $this->enrollmentService->addClassToKrs($mhs, $semester, $kelas1);

        // Tambah kelas 2 -> Bentrok!
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('bentrok');

        $this->enrollmentService->addClassToKrs($mhs, $semester, $kelas2);
    }

    public function test_r4_capacity_full_fails(): void
    {
        $mhs = Mahasiswa::first();
        $semester = Semester::first();
        $dosen = Dosen::first();
        $prodi = Prodi::first();

        $mk = MataKuliah::create(['kode' => 'MK-FULL', 'nama' => 'Matkul Penuh', 'sks' => 3, 'semester_penawaran' => 'ganjil', 'prodi_id' => $prodi->id]);
        $kelas = Kelas::create(['kode' => 'K-FULL', 'mata_kuliah_id' => $mk->id, 'semester_id' => $semester->id, 'dosen_id' => $dosen->id, 'kapasitas' => 1, 'ruang' => 'R1']);

        // Isi 1 mahasiswa lain
        $userMhs2 = User::create(['name' => 'Mhs 2', 'email' => 'mhs2@siakad.ac.id', 'password' => 'password']);
        $mhs2 = Mahasiswa::create([
            'user_id' => $userMhs2->id,
            'nim' => '22001002',
            'prodi_id' => $prodi->id,
            'angkatan' => 2022,
            'status' => StatusMahasiswaEnum::AKTIF,
            'tanggal_masuk' => '2022-09-01',
        ]);

        $this->enrollmentService->addClassToKrs($mhs2, $semester, $kelas);

        // Mahasiswa ke-2 mencoba mengambil kelas yang sudah penuh kapasitas (1/1)
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Kapasitas kelas K-FULL sudah penuh (1/1)');

        $this->enrollmentService->addClassToKrs($mhs, $semester, $kelas);
    }

    public function test_r6_ukt_gate_approval(): void
    {
        $mhs = Mahasiswa::first();
        $semester = Semester::first();
        $admin = User::where('email', 'admin@siakad.ac.id')->first();

        $krs = Krs::create(['mahasiswa_id' => $mhs->id, 'semester_id' => $semester->id, 'total_sks' => 3, 'status' => KrsStatusEnum::MENUNGGU]);

        // 1. UKT Belum Lunas
        Pembayaran::where('mahasiswa_id', $mhs->id)->where('semester_id', $semester->id)->update(['status' => PembayaranStatusEnum::BELUM]);

        try {
            $this->approvalService->approve($krs, $admin);
            $this->fail('Approval harusnya gagal karena UKT belum lunas.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('pembayaran UKT semester ini belum Lunas', $e->getMessage());
        }

        // 2. Ubah UKT Lunas
        Pembayaran::where('mahasiswa_id', $mhs->id)->where('semester_id', $semester->id)->update(['status' => PembayaranStatusEnum::LUNAS]);

        $this->approvalService->approve($krs, $admin);
        $this->assertEquals(KrsStatusEnum::DISETUJUI, $krs->fresh()->status);
        $this->assertEquals($admin->id, $krs->fresh()->disetujui_oleh);
    }

    public function test_r9_r10_ipk_ips_calculator(): void
    {
        $mhs = Mahasiswa::first();
        $semester = Semester::first();
        $dosen = Dosen::first();
        $prodi = Prodi::first();

        $krs = Krs::create(['mahasiswa_id' => $mhs->id, 'semester_id' => $semester->id, 'total_sks' => 7, 'status' => KrsStatusEnum::DISETUJUI]);

        // MK 1: 3 SKS, Nilai A (Bobot 4.00) -> Mutu 12
        $mk1 = MataKuliah::create(['kode' => 'MK-A', 'nama' => 'MK A', 'sks' => 3, 'semester_penawaran' => 'ganjil', 'prodi_id' => $prodi->id]);
        $kelas1 = Kelas::create(['kode' => 'K-A', 'mata_kuliah_id' => $mk1->id, 'semester_id' => $semester->id, 'dosen_id' => $dosen->id, 'kapasitas' => 30, 'ruang' => 'R1']);
        $detail1 = KrsDetail::create(['krs_id' => $krs->id, 'kelas_id' => $kelas1->id, 'status' => KrsDetailStatusEnum::DIAMBIL]);
        Nilai::create(['krs_detail_id' => $detail1->id, 'nilai_huruf' => NilaiHurufEnum::A, 'bobot' => 4.00, 'sks' => 3, 'lulus' => true, 'tahun_akademik' => '2025/2026']);

        // MK 2: 4 SKS, Nilai B (Bobot 3.00) -> Mutu 12
        $mk2 = MataKuliah::create(['kode' => 'MK-B', 'nama' => 'MK B', 'sks' => 4, 'semester_penawaran' => 'ganjil', 'prodi_id' => $prodi->id]);
        $kelas2 = Kelas::create(['kode' => 'K-B', 'mata_kuliah_id' => $mk2->id, 'semester_id' => $semester->id, 'dosen_id' => $dosen->id, 'kapasitas' => 30, 'ruang' => 'R2']);
        $detail2 = KrsDetail::create(['krs_id' => $krs->id, 'kelas_id' => $kelas2->id, 'status' => KrsDetailStatusEnum::DIAMBIL]);
        Nilai::create(['krs_detail_id' => $detail2->id, 'nilai_huruf' => NilaiHurufEnum::B, 'bobot' => 3.00, 'sks' => 4, 'lulus' => true, 'tahun_akademik' => '2025/2026']);

        // Total Mutu = 12 + 12 = 24. Total SKS = 7. IPS = 24 / 7 = 3.42857... -> 3.43
        $ips = $this->ipkCalculator->calculateIps($mhs, $semester);
        $this->assertEquals(3.43, $ips);
    }
}
