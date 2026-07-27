<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Nilai;
use App\Models\User;

class NilaiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::KAPRODI, RoleEnum::DOSEN, RoleEnum::MAHASISWA]);
    }

    public function view(User $user, Nilai $nilai): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            return $user->dosen->prodi_id === $nilai->krsDetail->kelas->mataKuliah->prodi_id;
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $user->dosen->id === $nilai->krsDetail->kelas->dosen_id;
        }

        return $user->mahasiswa && $user->mahasiswa->id === $nilai->krsDetail->krs->mahasiswa_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole([RoleEnum::ADMIN, RoleEnum::DOSEN, RoleEnum::KAPRODI]);
    }

    public function update(User $user, Nilai $nilai): bool
    {
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::DOSEN) && $user->dosen) {
            return $user->dosen->id === $nilai->krsDetail->kelas->dosen_id;
        }

        return false;
    }

    public function delete(User $user, Nilai $nilai): bool
    {
        return $user->hasRole(RoleEnum::ADMIN);
    }
}
