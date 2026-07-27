<?php

namespace App\Livewire\Portal;

use App\Enums\KrsDetailStatusEnum;
use App\Enums\NilaiHurufEnum;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Pembayaran;
use App\Models\Semester;
use App\Services\EnrollmentService;
use App\Services\IpkCalculator;
use App\Services\KrsApprovalService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DashboardComponent extends Component
{
    public string $activeTab = 'krs';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    // KHS Tab State
    public ?int $selectedSemesterId = null;

    // Dosen Tab State
    public ?int $selectedKelasId = null;

    public array $inputNilai = []; // [krs_detail_id => nilai_huruf]

    public function mount()
    {
        $activeSemester = Semester::where('is_active', true)->first();
        if ($activeSemester) {
            $this->selectedSemesterId = $activeSemester->id;
        }
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    public function ambilKelas(int $kelasId)
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $user = auth()->user();
        if (! $user || ! $user->mahasiswa) {
            $this->errorMessage = 'Anda tidak terdaftar sebagai Mahasiswa aktif.';
            return;
        }

        $mahasiswa = $user->mahasiswa;
        $activeSemester = Semester::where('is_active', true)->first();

        if (! $activeSemester) {
            $this->errorMessage = 'Tidak ada semester aktif saat ini.';
            return;
        }

        $kelas = Kelas::find($kelasId);
        if (! $kelas) {
            $this->errorMessage = 'Kelas perkuliahan tidak ditemukan.';
            return;
        }

        try {
            $enrollmentService = app(EnrollmentService::class);
            $enrollmentService->addClassToKrs($mahasiswa, $activeSemester, $kelas);

            $this->successMessage = "Berhasil mengambil kelas {$kelas->kode} ({$kelas->mataKuliah->nama}).";
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first() ?? $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function batalkanKelas(int $krsDetailId)
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $detail = KrsDetail::find($krsDetailId);
        if ($detail) {
            $enrollmentService = app(EnrollmentService::class);
            $enrollmentService->removeClassFromKrs($detail);

            $this->successMessage = 'Kelas berhasil dibatalkan dari KRS.';
        }
    }

    public function ajukanKrs(int $krsId)
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $krs = Krs::find($krsId);
        if (! $krs) {
            return;
        }

        try {
            $approvalService = app(KrsApprovalService::class);
            $approvalService->submitForApproval($krs);

            $this->successMessage = 'KRS berhasil diajukan! Menunggu persetujuan Dosen Wali / Kaprodi.';
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first() ?? $e->getMessage();
        }
    }

    public function simpanNilaiDosen()
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        if (empty($this->inputNilai)) {
            $this->errorMessage = 'Tidak ada nilai yang diubah.';
            return;
        }

        $updatedCount = 0;
        foreach ($this->inputNilai as $krsDetailId => $nilaiHuruf) {
            if (empty($nilaiHuruf)) {
                continue;
            }

            $detail = KrsDetail::find($krsDetailId);
            if (! $detail) {
                continue;
            }

            $enum = NilaiHurufEnum::tryFrom($nilaiHuruf);
            if (! $enum) {
                continue;
            }

            Nilai::updateOrCreate(
                ['krs_detail_id' => $detail->id],
                [
                    'nilai_huruf' => $enum,
                    'bobot' => $enum->bobot(),
                    'sks' => $detail->kelas->mataKuliah->sks ?? 0,
                    'lulus' => $enum->isLulus(),
                    'tahun_akademik' => $detail->kelas->semester->nama ?? '2025/2026',
                ]
            );

            $updatedCount++;
        }

        $this->successMessage = "Berhasil menyimpan {$updatedCount} nilai mahasiswa.";
    }

    public function render()
    {
        $user = auth()->user();
        $mahasiswa = $user?->mahasiswa;
        $dosen = $user?->dosen;
        $activeSemester = Semester::where('is_active', true)->first();

        $calculator = app(IpkCalculator::class);

        // Data Mahasiswa
        $ipk = $mahasiswa ? $calculator->calculateIpk($mahasiswa) : 0.00;
        $maxSks = ($mahasiswa && $activeSemester) ? $calculator->getMaxSksAllowed($mahasiswa, $activeSemester) : 20;

        $krsAktif = ($mahasiswa && $activeSemester)
            ? Krs::where('mahasiswa_id', $mahasiswa->id)->where('semester_id', $activeSemester->id)->first()
            : null;

        $statusUkt = ($mahasiswa && $activeSemester)
            ? Pembayaran::where('mahasiswa_id', $mahasiswa->id)->where('semester_id', $activeSemester->id)->first()?->status?->label() ?? 'Belum Lunas'
            : '-';

        // Kelas Ditawarkan
        $kelasDitawarkan = $activeSemester ? Kelas::where('semester_id', $activeSemester->id)->with(['mataKuliah', 'dosen.user', 'jadwals'])->get() : collect();

        // KHS Data
        $allSemesters = Semester::orderBy('tahun', 'desc')->get();
        $khsNilais = collect();
        $khsIps = 0.00;

        if ($mahasiswa && $this->selectedSemesterId) {
            $targetSemester = Semester::find($this->selectedSemesterId);
            if ($targetSemester) {
                $khsNilais = Nilai::whereHas('krsDetail.krs', function ($q) use ($mahasiswa, $targetSemester) {
                    $q->where('mahasiswa_id', $mahasiswa->id)->where('semester_id', $targetSemester->id);
                })->with(['krsDetail.kelas.mataKuliah'])->get();

                $khsIps = $calculator->calculateIps($mahasiswa, $targetSemester);
            }
        }

        // Data Dosen Ampuan
        $dosenKelasList = $dosen ? Kelas::where('dosen_id', $dosen->id)->where('semester_id', $activeSemester?->id)->with('mataKuliah')->get() : collect();
        $selectedKelas = $this->selectedKelasId ? Kelas::find($this->selectedKelasId) : $dosenKelasList->first();

        $pesertaKelasList = $selectedKelas ? KrsDetail::where('kelas_id', $selectedKelas->id)->where('status', KrsDetailStatusEnum::DIAMBIL->value)->with(['krs.mahasiswa.user', 'nilai'])->get() : collect();

        return view('livewire.portal.dashboard-component', compact(
            'user',
            'mahasiswa',
            'dosen',
            'activeSemester',
            'ipk',
            'maxSks',
            'krsAktif',
            'statusUkt',
            'kelasDitawarkan',
            'allSemesters',
            'khsNilais',
            'khsIps',
            'dosenKelasList',
            'selectedKelas',
            'pesertaKelasList'
        ))->layout('layouts.portal');
    }
}
