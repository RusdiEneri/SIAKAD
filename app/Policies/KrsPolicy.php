<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Krs;
use App\Models\User;

class KrsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN, RoleEnum::MAHASISWA]);
    }

    public function view(User $user, Krs $krs): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $krs->mahasiswa->prodi_id;
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $user->dosen->id === $krs->mahasiswa->dosen_wali_id;
        }

        return $user->mahasiswa && $user->mahasiswa->id === $krs->mahasiswa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::MAHASISWA]);
    }

    public function update(User $user, Krs $krs): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $krs->mahasiswa->prodi_id;
        }

        return $user->mahasiswa && $user->mahasiswa->id === $krs->mahasiswa_id;
    }

    public function approve(User $user, Krs $krs): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $krs->mahasiswa->prodi_id;
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $user->dosen->id === $krs->mahasiswa->dosen_wali_id;
        }

        return false;
    }

    public function delete(User $user, Krs $krs): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
