<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Kelas;

class ScheduleConflictChecker
{
    /**
     * Memeriksa apakah kelas target bentrok jadwal dengan daftar kelas yang sudah diambil (R3).
     * Returns null jika tidak bentrok, atau array detail bentrok jika ditemukan bentrok.
     */
    public function checkConflict(Kelas $targetKelas, array $existingKelasIds): ?array
    {
        if (empty($existingKelasIds)) {
            return null;
        }

        // Ambil seluruh jadwal dari kelas target
        $targetJadwals = $targetKelas->jadwals;

        // Ambil seluruh jadwal dari kelas-kelas yang sudah ada di KRS
        $existingJadwals = Jadwal::query()
            ->with(['kelas.mataKuliah'])
            ->whereIn('kelas_id', $existingKelasIds)
            ->get();

        foreach ($targetJadwals as $targetJadwal) {
            foreach ($existingJadwals as $existingJadwal) {
                // Samakan Hari (Enum value comparison)
                $targetHari = $targetJadwal->hari instanceof \BackedEnum ? $targetJadwal->hari->value : $targetJadwal->hari;
                $existingHari = $existingJadwal->hari instanceof \BackedEnum ? $existingJadwal->hari->value : $existingJadwal->hari;

                if ($targetHari === $existingHari) {
                    // Rumus Bentrok Jam: (StartA < EndB) AND (EndA > StartB)
                    $targetStart = $targetJadwal->jam_mulai;
                    $targetEnd = $targetJadwal->jam_selesai;
                    $existingStart = $existingJadwal->jam_mulai;
                    $existingEnd = $existingJadwal->jam_selesai;

                    if ($targetStart < $existingEnd && $targetEnd > $existingStart) {
                        return [
                            'target_kelas' => $targetKelas,
                            'target_jadwal' => $targetJadwal,
                            'existing_kelas' => $existingJadwal->kelas,
                            'existing_jadwal' => $existingJadwal,
                            'message' => sprintf(
                                "Jadwal kelas %s (%s, %s - %s) bentrok dengan kelas %s (%s - %s) yang sudah ada di KRS Anda.",
                                $targetKelas->mataKuliah->nama ?? $targetKelas->kode,
                                ucfirst($targetHari),
                                substr($targetStart, 0, 5),
                                substr($targetEnd, 0, 5),
                                $existingJadwal->kelas->mataKuliah->nama ?? $existingJadwal->kelas->kode,
                                substr($existingStart, 0, 5),
                                substr($existingEnd, 0, 5)
                            ),
                        ];
                    }
                }
            }
        }

        return null;
    }
}
