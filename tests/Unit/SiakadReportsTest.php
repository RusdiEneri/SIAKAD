<?php

namespace Tests\Unit;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_krs_report_renders_successfully(): void
    {
        $admin = User::where('email', 'admin@siakad.ac.id')->first();
        $mhs = Mahasiswa::first();
        $semester = Semester::first();

        $krs = Krs::create([
            'mahasiswa_id' => $mhs->id,
            'semester_id' => $semester->id,
            'total_sks' => 0,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('reports.krs', $krs));
        $response->assertStatus(200);
        $response->assertSee($mhs->nim);
        $response->assertSee('KARTU RENCANA STUDI');
    }

    public function test_khs_report_renders_successfully(): void
    {
        $admin = User::where('email', 'admin@siakad.ac.id')->first();
        $mhs = Mahasiswa::first();
        $semester = Semester::first();

        $response = $this->actingAs($admin)->get(route('reports.khs', ['mahasiswa' => $mhs->id, 'semester' => $semester->id]));
        $response->assertStatus(200);
        $response->assertSee($mhs->nim);
        $response->assertSee('KARTU HASIL STUDI');
    }

    public function test_transkrip_report_renders_successfully(): void
    {
        $admin = User::where('email', 'admin@siakad.ac.id')->first();
        $mhs = Mahasiswa::first();

        $response = $this->actingAs($admin)->get(route('reports.transkrip', $mhs));
        $response->assertStatus(200);
        $response->assertSee($mhs->nim);
        $response->assertSee('TRANSKRIP AKADEMIK KUMULATIF');
    }
}
