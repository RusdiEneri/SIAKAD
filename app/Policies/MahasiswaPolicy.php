<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Mahasiswa;
use App\Models\User;

class MahasiswaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN]);
    }

    public function view(User $user, Mahasiswa $mahasiswa): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $mahasiswa->prodi_id;
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $user->dosen->id === $mahasiswa->dosen_wali_id;
        }

        return $user->id === $mahasiswa->user_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }

    public function update(User $user, Mahasiswa $mahasiswa): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $mahasiswa->prodi_id;
        }

        return $user->id === $mahasiswa->user_id;
    }

    public function delete(User $user, Mahasiswa $mahasiswa): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
