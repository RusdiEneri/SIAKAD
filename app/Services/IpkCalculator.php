<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Semester;

class IpkCalculator
{
    /**
     * Menghitung IPS (Indeks Prestasi Semester) mahasiswa untuk semester tertentu.
     */
    public function calculateIps(Mahasiswa $mahasiswa, Semester $semester): float
    {
        $nilais = Nilai::query()
            ->whereHas('krsDetail.krs', function ($query) use ($mahasiswa, $semester) {
                $query->where('mahasiswa_id', $mahasiswa->id)
                    ->where('semester_id', $semester->id);
            })
            ->get();

        if ($nilais->isEmpty()) {
            return 0.00;
        }

        $totalMutu = 0.00;
        $totalSks = 0;

        foreach ($nilais as $nilai) {
            $totalMutu += (float) $nilai->bobot * (int) $nilai->sks;
            $totalSks += (int) $nilai->sks;
        }

        if ($totalSks === 0) {
            return 0.00;
        }

        return round($totalMutu / $totalSks, 2);
    }

    /**
     * Menghitung IPK (Indeks Prestasi Kumulatif) mahasiswa seluruh semester.
     */
    public function calculateIpk(Mahasiswa $mahasiswa): float
    {
        $nilais = Nilai::query()
            ->whereHas('krsDetail.krs', function ($query) use ($mahasiswa) {
                $query->where('mahasiswa_id', $mahasiswa->id);
            })
            ->get();

        if ($nilais->isEmpty()) {
            return 0.00;
        }

        $totalMutu = 0.00;
        $totalSks = 0;

        foreach ($nilais as $nilai) {
            $totalMutu += (float) $nilai->bobot * (int) $nilai->sks;
            $totalSks += (int) $nilai->sks;
        }

        if ($totalSks === 0) {
            return 0.00;
        }

        return round($totalMutu / $totalSks, 2);
    }

    /**
     * Menentukan jatah SKS maksimum yang boleh diambil mahasiswa pada semester aktif (R1).
     */
    public function getMaxSksAllowed(Mahasiswa $mahasiswa, Semester $activeSemester): int
    {
        // Cari semester yang pernah diikuti mahasiswa sebelum semester aktif ini
        $prevSemester = Semester::query()
            ->where('tanggal_akhir', '<', $activeSemester->tanggal_mulai)
            ->whereHas('krs', function ($query) use ($mahasiswa) {
                $query->where('mahasiswa_id', $mahasiswa->id);
            })
            ->orderBy('tanggal_akhir', 'desc')
            ->first();

        if (! $prevSemester) {
            // Mahasiswa semester 1 / belum ada histori semester lalu
            return (int) config('siakad.sks_tiers.default_first_semester', 20);
        }

        $gpa = $this->calculateIps($mahasiswa, $prevSemester);

        if ($gpa == 0.00) {
            $gpa = $this->calculateIpk($mahasiswa);
        }

        $tiers = config('siakad.sks_tiers.rules', []);

        foreach ($tiers as $tier) {
            if ($gpa >= $tier['min_gpa']) {
                return (int) $tier['max_sks'];
            }
        }

        return 15;
    }
}
