<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Semester;
use App\Services\IpkCalculator;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function cetakKrs(Krs $krs)
    {
        $krs->load(['mahasiswa.user', 'mahasiswa.prodi', 'semester', 'approver', 'details.kelas.mataKuliah', 'details.kelas.dosen.user', 'details.kelas.jadwals']);

        return view('reports.krs', compact('krs'));
    }

    public function cetakKhs(Mahasiswa $mahasiswa, Semester $semester, IpkCalculator $calculator)
    {
        $mahasiswa->load(['user', 'prodi', 'dosenWali.user']);

        $nilais = Nilai::query()
            ->whereHas('krsDetail.krs', function ($q) use ($mahasiswa, $semester) {
                $q->where('mahasiswa_id', $mahasiswa->id)
                    ->where('semester_id', $semester->id);
            })
            ->with(['krsDetail.kelas.mataKuliah'])
            ->get();

        $ips = $calculator->calculateIps($mahasiswa, $semester);
        $ipk = $calculator->calculateIpk($mahasiswa);

        return view('reports.khs', compact('mahasiswa', 'semester', 'nilais', 'ips', 'ipk'));
    }

    public function cetakTranskrip(Mahasiswa $mahasiswa, IpkCalculator $calculator)
    {
        $mahasiswa->load(['user', 'prodi', 'dosenWali.user']);

        $nilais = Nilai::query()
            ->whereHas('krsDetail.krs', function ($q) use ($mahasiswa) {
                $q->where('mahasiswa_id', $mahasiswa->id);
            })
            ->with(['krsDetail.kelas.mataKuliah'])
            ->get();

        $ipk = $calculator->calculateIpk($mahasiswa);
        $totalSksLulus = $nilais->where('lulus', true)->sum('sks');

        return view('reports.transkrip', compact('mahasiswa', 'nilais', 'ipk', 'totalSksLulus'));
    }
}
