<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\KrsDetailStatusEnum;
use App\Enums\KrsStatusEnum;
use App\Enums\SemesterPenawaranEnum;
use App\Enums\StatusMahasiswaEnum;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function __construct(
        protected IpkCalculator $ipkCalculator,
        protected ScheduleConflictChecker $conflictChecker
    ) {}

    /**
     * Menambahkan kelas ke KRS Mahasiswa dengan penegakan aturan R1 - R8.
     */
    public function addClassToKrs(Mahasiswa $mahasiswa, Semester $semester, Kelas $kelas): KrsDetail
    {
        // R8: Status Mahasiswa Harus Aktif
        $mhsStatus = $mahasiswa->status instanceof \BackedEnum ? $mahasiswa->status->value : $mahasiswa->status;
        if ($mhsStatus !== StatusMahasiswaEnum::AKTIF->value) {
            throw ValidationException::withMessages([
                'mahasiswa' => sprintf(
                    'Status akademik Anda saat ini adalah %s. Anda tidak diizinkan menyusun KRS.',
                    ucfirst($mhsStatus)
                ),
            ]);
        }

        // R5: Kesesuaian Semester Penawaran
        $mk = $kelas->mataKuliah;
        $mkPenawaran = $mk->semester_penawaran instanceof \BackedEnum ? $mk->semester_penawaran->value : $mk->semester_penawaran;
        $semesterJenis = $semester->jenis instanceof \BackedEnum ? $semester->jenis->value : $semester->jenis;

        if ($mkPenawaran !== SemesterPenawaranEnum::SEMUA->value && $mkPenawaran !== $semesterJenis) {
            throw ValidationException::withMessages([
                'kelas' => sprintf(
                    'Mata kuliah %s hanya ditawarkan pada semester %s.',
                    $mk->nama,
                    ucfirst($mkPenawaran)
                ),
            ]);
        }

        // Ambil atau buat header KRS semester aktif
        $krs = Krs::firstOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id' => $semester->id,
            ],
            [
                'total_sks' => 0,
                'status' => KrsStatusEnum::DRAFT,
            ]
        );

        // Ambil kelas-kelas yang sudah ada di KRS semester aktif ini
        $existingDetails = KrsDetail::query()
            ->where('krs_id', $krs->id)
            ->where('status', KrsDetailStatusEnum::DIAMBIL->value)
            ->with(['kelas.mataKuliah'])
            ->get();

        $existingKelasIds = $existingDetails->pluck('kelas_id')->all();

        // R7 (a): Cegah ambil kelas duplikat atau MK sama di semester sama
        foreach ($existingDetails as $detail) {
            if ($detail->kelas_id === $kelas->id) {
                throw ValidationException::withMessages([
                    'kelas' => sprintf('Anda sudah mengambil kelas %s pada KRS semester ini.', $kelas->kode),
                ]);
            }

            if ($detail->kelas->mata_kuliah_id === $kelas->mata_kuliah_id) {
                throw ValidationException::withMessages([
                    'kelas' => sprintf(
                        'Anda sudah mengambil mata kuliah %s (%s) di kelas lain pada semester ini.',
                        $mk->nama,
                        $detail->kelas->kode
                    ),
                ]);
            }
        }

        // R7 (b): Cegah ambil mata kuliah yang sudah Pernah LULUS di semester terdahulu
        $alreadyPassed = Nilai::query()
            ->where('lulus', true)
            ->whereHas('krsDetail', function ($query) use ($mahasiswa, $kelas) {
                $query->whereHas('krs', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                    ->whereHas('kelas', fn ($q) => $q->where('mata_kuliah_id', $kelas->mata_kuliah_id));
            })
            ->exists();

        if ($alreadyPassed) {
            throw ValidationException::withMessages([
                'kelas' => sprintf('Anda sudah lulus mata kuliah %s pada semester sebelumnya.', $mk->nama),
            ]);
        }

        // R2: Validasi Kelulusan Mata Kuliah Prasyarat
        $prasyaratMks = $mk->prasyarat;
        if ($prasyaratMks->isNotEmpty()) {
            $unpassedPrasyarats = [];

            foreach ($prasyaratMks as $prasyarat) {
                $isPassed = Nilai::query()
                    ->where('lulus', true)
                    ->whereHas('krsDetail', function ($query) use ($mahasiswa, $prasyarat) {
                        $query->whereHas('krs', fn ($q) => $q->where('mahasiswa_id', $mahasiswa->id))
                            ->whereHas('kelas', fn ($q) => $q->where('mata_kuliah_id', $prasyarat->id));
                    })
                    ->exists();

                if (! $isPassed) {
                    $unpassedPrasyarats[] = sprintf('%s (%s)', $prasyarat->nama, $prasyarat->kode);
                }
            }

            if (! empty($unpassedPrasyarats)) {
                throw ValidationException::withMessages([
                    'prasyarat' => sprintf(
                        'Mata kuliah %s membutuhkan prasyarat: %s yang belum Anda lulusi.',
                        $mk->nama,
                        implode(', ', $unpassedPrasyarats)
                    ),
                ]);
            }
        }

        // R4: Pembatasan Kapasitas Kelas
        $enrolledCount = KrsDetail::query()
            ->where('kelas_id', $kelas->id)
            ->where('status', KrsDetailStatusEnum::DIAMBIL->value)
            ->count();

        if ($enrolledCount >= $kelas->kapasitas) {
            throw ValidationException::withMessages([
                'kapasitas' => sprintf(
                    'Kapasitas kelas %s sudah penuh (%d/%d).',
                    $kelas->kode,
                    $enrolledCount,
                    $kelas->kapasitas
                ),
            ]);
        }

        // R3: Validasi Bentrok Jadwal
        $conflictResult = $this->conflictChecker->checkConflict($kelas, $existingKelasIds);
        if ($conflictResult !== null) {
            throw ValidationException::withMessages([
                'jadwal' => $conflictResult['message'],
            ]);
        }

        // R1: Batas Maksimum SKS Berdasarkan IPK/IPS
        $maxSksAllowed = $this->ipkCalculator->getMaxSksAllowed($mahasiswa, $semester);
        $currentTotalSks = (int) $existingDetails->sum(fn ($d) => $d->kelas->mataKuliah->sks);
        $newTotalSks = $currentTotalSks + (int) $mk->sks;

        if ($newTotalSks > $maxSksAllowed) {
            throw ValidationException::withMessages([
                'sks' => sprintf(
                    'Total SKS (%d) melebihi batas maksimum SKS Anda (%d SKS) untuk semester ini.',
                    $newTotalSks,
                    $maxSksAllowed
                ),
            ]);
        }

        // Eksekusi penyimpanan dalam DB Transaction
        return DB::transaction(function () use ($krs, $kelas, $newTotalSks) {
            $detail = KrsDetail::create([
                'krs_id' => $krs->id,
                'kelas_id' => $kelas->id,
                'status' => KrsDetailStatusEnum::DIAMBIL,
            ]);

            $krs->update(['total_sks' => $newTotalSks]);

            return $detail;
        });
    }

    /**
     * Membatalkan/menghapus kelas dari KRS.
     */
    public function removeClassFromKrs(KrsDetail $detail): void
    {
        DB::transaction(function () use ($detail) {
            $krs = $detail->krs;
            $sksDibatalkan = $detail->kelas->mataKuliah->sks ?? 0;

            $detail->delete();

            $newTotalSks = max(0, (int) $krs->total_sks - (int) $sksDibatalkan);
            $krs->update(['total_sks' => $newTotalSks]);
        });
    }
}
