<?php

namespace Tests\Unit;

use App\Enums\HariEnum;
use App\Enums\KrsStatusEnum;
use App\Enums\NilaiHurufEnum;
use App\Enums\PembayaranStatusEnum;
use App\Enums\RoleEnum;
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
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_required_roles_and_users(): void
    {
        $this->seed();

        $this->assertDatabaseHas('roles', ['name' => RoleEnum::ADMIN->value]);
        $this->assertDatabaseHas('roles', ['name' => RoleEnum::KAPRODI->value]);
        $this->assertDatabaseHas('roles', ['name' => RoleEnum::DOSEN->value]);
        $this->assertDatabaseHas('roles', ['name' => RoleEnum::MAHASISWA->value]);
        $this->assertDatabaseHas('roles', ['name' => RoleEnum::KEUANGAN->value]);

        $admin = User::where('email', 'admin@siakad.ac.id')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole(RoleEnum::ADMIN));

        $kaprodi = User::where('email', 'kaprodi@siakad.ac.id')->first();
        $this->assertNotNull($kaprodi);
        $this->assertTrue($kaprodi->hasRole(RoleEnum::KAPRODI));
        $this->assertTrue($kaprodi->hasRole(RoleEnum::DOSEN));

        $dosen = User::where('email', 'dosen@siakad.ac.id')->first();
        $this->assertNotNull($dosen);
        $this->assertTrue($dosen->hasRole(RoleEnum::DOSEN));

        $mahasiswa = User::where('email', 'mahasiswa@siakad.ac.id')->first();
        $this->assertNotNull($mahasiswa);
        $this->assertTrue($mahasiswa->hasRole(RoleEnum::MAHASISWA));

        $keuangan = User::where('email', 'keuangan@siakad.ac.id')->first();
        $this->assertNotNull($keuangan);
        $this->assertTrue($keuangan->hasRole(RoleEnum::KEUANGAN));
    }

    public function test_eloquent_relationships_work_correctly(): void
    {
        $this->seed();

        $mhs = Mahasiswa::where('nim', '22001001')->first();
        $this->assertNotNull($mhs);
        $this->assertEquals(StatusMahasiswaEnum::AKTIF, $mhs->status);
        $this->assertNotNull($mhs->dosenWali);
        $this->assertEquals('Teknik Informatika', $mhs->prodi->nama);

        $mkAlgo2 = MataKuliah::where('kode', 'TIF201')->first();
        $this->assertNotNull($mkAlgo2);
        $this->assertCount(1, $mkAlgo2->prasyarat);
        $this->assertEquals('TIF101', $mkAlgo2->prasyarat->first()->kode);

        $pembayaran = Pembayaran::where('mahasiswa_id', $mhs->id)->first();
        $this->assertNotNull($pembayaran);
        $this->assertEquals(PembayaranStatusEnum::LUNAS, $pembayaran->status);
    }

    public function test_soft_deletes_on_transactional_models(): void
    {
        $this->seed();

        $mhs = Mahasiswa::first();
        $semester = Semester::first();
        $kelas = Kelas::first();

        $krs = Krs::create([
            'mahasiswa_id' => $mhs->id,
            'semester_id' => $semester->id,
            'total_sks' => 3,
            'status' => KrsStatusEnum::DRAFT,
        ]);

        $krsDetail = KrsDetail::create([
            'krs_id' => $krs->id,
            'kelas_id' => $kelas->id,
        ]);

        $nilai = Nilai::create([
            'krs_detail_id' => $krsDetail->id,
            'nilai_huruf' => NilaiHurufEnum::A,
            'bobot' => 4.00,
            'sks' => 3,
            'lulus' => true,
            'tahun_akademik' => '2025/2026',
        ]);

        // Soft delete test
        $krs->delete();
        $this->assertSoftDeleted('krs', ['id' => $krs->id]);

        $krsDetail->delete();
        $this->assertSoftDeleted('krs_details', ['id' => $krsDetail->id]);

        $nilai->delete();
        $this->assertSoftDeleted('nilais', ['id' => $nilai->id]);
    }
}
