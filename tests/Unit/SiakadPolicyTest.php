<?php

namespace Tests\Unit;

use App\Filament\Resources\KrsResource;
use App\Filament\Resources\MahasiswaResource;
use App\Filament\Resources\PembayaranResource;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_has_full_query_scope(): void
    {
        $admin = User::where('email', 'admin@siakad.ac.id')->first();
        $this->actingAs($admin);

        $mhsCount = MahasiswaResource::getEloquentQuery()->count();
        $this->assertEquals(Mahasiswa::count(), $mhsCount);
    }

    public function test_mahasiswa_scoped_only_to_own_records(): void
    {
        $mhsUser = User::where('email', 'mahasiswa@siakad.ac.id')->first();
        $this->actingAs($mhsUser);

        // Scope Mahasiswa
        $scopedMhs = MahasiswaResource::getEloquentQuery()->get();
        $this->assertCount(1, $scopedMhs);
        $this->assertEquals($mhsUser->id, $scopedMhs->first()->user_id);

        // Scope Pembayaran
        $scopedPembayarans = PembayaranResource::getEloquentQuery()->get();
        $this->assertCount(1, $scopedPembayarans);
        $this->assertEquals($mhsUser->mahasiswa->id, $scopedPembayarans->first()->mahasiswa_id);
    }

    public function test_kaprodi_scoped_to_prodi_students_and_krs(): void
    {
        $kaprodiUser = User::where('email', 'kaprodi@siakad.ac.id')->first();
        $this->actingAs($kaprodiUser);

        $scopedMhs = MahasiswaResource::getEloquentQuery()->get();
        foreach ($scopedMhs as $mhs) {
            $this->assertEquals($kaprodiUser->dosen->prodi_id, $mhs->prodi_id);
        }
    }

    public function test_keuangan_scoped_only_to_pembayaran(): void
    {
        $keuanganUser = User::where('email', 'keuangan@siakad.ac.id')->first();
        $this->actingAs($keuanganUser);

        $scopedPembayarans = PembayaranResource::getEloquentQuery()->get();
        $this->assertEquals(Pembayaran::count(), $scopedPembayarans->count());

        $scopedKrs = KrsResource::getEloquentQuery()->get();
        $this->assertCount(0, $scopedKrs);
    }
}
