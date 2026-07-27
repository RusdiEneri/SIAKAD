<?php

namespace Tests\Unit;

use App\Filament\Resources\DosenResource;
use App\Filament\Resources\MahasiswaResource;
use App\Filament\Resources\MataKuliahResource;
use App\Filament\Resources\ProdiResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\SemesterResource;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_master_data_resources_exist_and_render_index(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@siakad.ac.id')->first();
        $this->actingAs($admin);

        $resources = [
            UserResource::class,
            RoleResource::class,
            ProdiResource::class,
            DosenResource::class,
            MahasiswaResource::class,
            MataKuliahResource::class,
            SemesterResource::class,
        ];

        foreach ($resources as $resource) {
            $this->assertNotEmpty($resource::getNavigationLabel());
            $this->assertNotEmpty($resource::getModel());
        }
    }

    public function test_master_data_models_can_be_created_via_eloquent(): void
    {
        $this->seed();

        $userCount = User::count();
        $prodiCount = ProdiResource::getModel()::count();

        $this->assertGreaterThan(0, $userCount);
        $this->assertGreaterThan(0, $prodiCount);
    }
}
