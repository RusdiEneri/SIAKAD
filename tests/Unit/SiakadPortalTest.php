<?php

namespace Tests\Unit;

use App\Livewire\Portal\DashboardComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiakadPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_mahasiswa_can_access_portal_dashboard(): void
    {
        $mhsUser = User::where('email', 'mahasiswa@siakad.ac.id')->first();
        $this->actingAs($mhsUser);

        $response = $this->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Mahasiswa');
        $response->assertSee('IPK Kumulatif');
    }

    public function test_dosen_can_access_portal_dashboard(): void
    {
        $dosenUser = User::where('email', 'dosen@siakad.ac.id')->first();
        $this->actingAs($dosenUser);

        $response = $this->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso, M.T.');
    }

    public function test_livewire_portal_dashboard_component_renders(): void
    {
        $mhsUser = User::where('email', 'mahasiswa@siakad.ac.id')->first();

        Livewire::actingAs($mhsUser)
            ->test(DashboardComponent::class)
            ->assertSee('Status KRS')
            ->assertStatus(200);
    }
}
