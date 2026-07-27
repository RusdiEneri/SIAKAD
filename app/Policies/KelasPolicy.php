<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Kelas;
use App\Models\User;

class KelasPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN, RoleEnum::MAHASISWA]);
    }

    public function view(User $user, Kelas $kelas): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI]);
    }

    public function update(User $user, Kelas $kelas): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $kelas->mataKuliah->prodi_id;
        }

        return false;
    }

    public function delete(User $user, Kelas $kelas): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
