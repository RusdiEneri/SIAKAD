<?php

namespace App\Filament\Widgets;

use App\Enums\KrsStatusEnum;
use App\Enums\PembayaranStatusEnum;
use App\Enums\RoleEnum;
use App\Enums\StatusMahasiswaEnum;
use App\Models\Dosen;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Services\IpkCalculator;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiakadStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        // Dashboard Stats khusus Mahasiswa
        if ($user->hasRole(RoleEnum::MAHASISWA) && $user->mahasiswa) {
            $mhs = $user->mahasiswa;
            $calculator = new IpkCalculator();

            $ipk = $calculator->calculateIpk($mhs);
            $totalSksLulus = \App\Models\Nilai::query()
                ->where('lulus', true)
                ->whereHas('krsDetail.krs', fn ($q) => $q->where('mahasiswa_id', $mhs->id))
                ->sum('sks');

            $statusUkt = Pembayaran::query()
                ->where('mahasiswa_id', $mhs->id)
                ->latest()
                ->first()?->status?->label() ?? 'Belum ada tagihan';

            return [
                Stat::make('IPK Kumulatif', number_format($ipk, 2))
                    ->description('Indeks Prestasi Kumulatif')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('primary'),

                Stat::make('Total SKS Lulus', $totalSksLulus . ' SKS')
                    ->description('SKS Lulus Kumulatif')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success'),

                Stat::make('Status UKT Semester ini', $statusUkt)
                    ->description('Status Pembayaran UKT')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('warning'),
            ];
        }

        // Dashboard Stats untuk Admin / Kaprodi / Dosen / Keuangan
        $mhsQuery = Mahasiswa::query();
        $krsQuery = Krs::query();

        if ($user->hasRole(RoleEnum::KAPRODI) && $user->dosen) {
            $mhsQuery->where('prodi_id', $user->dosen->prodi_id);
            $krsQuery->whereHas('mahasiswa', fn ($q) => $q->where('prodi_id', $user->dosen->prodi_id));
        }

        $totalMhsAktif = $mhsQuery->where('status', StatusMahasiswaEnum::AKTIF->value)->count();
        $krsPending = $krsQuery->where('status', KrsStatusEnum::MENUNGGU->value)->count();
        $totalDosen = Dosen::where('is_active', true)->count();
        $totalUktLunas = Pembayaran::where('status', PembayaranStatusEnum::LUNAS->value)->count();

        return [
            Stat::make('Mahasiswa Aktif', $totalMhsAktif)
                ->description('Total Mahasiswa Aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('KRS Menunggu Approval', $krsPending)
                ->description('KRS Status Menunggu')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Dosen Aktif', $totalDosen)
                ->description('Dosen Penganti & Wali')
                ->descriptionIcon('heroicon-m-identification')
                ->color('info'),

            Stat::make('Pembayaran UKT Lunas', $totalUktLunas)
                ->description('Transaksi UKT Lunas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
