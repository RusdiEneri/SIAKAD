<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\KrsStatusEnum;
use App\Enums\PembayaranStatusEnum;
use App\Models\Krs;
use App\Models\Pembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KrsApprovalService
{
    /**
     * Menyetujui (Approve) KRS Mahasiswa dengan penegakan R6 (UKT Gate).
     */
    public function approve(Krs $krs, User $approver): void
    {
        $uktGateEnabled = (bool) config('siakad.ukt_gate_enabled', true);

        if ($uktGateEnabled) {
            $pembayaran = Pembayaran::query()
                ->where('mahasiswa_id', $krs->mahasiswa_id)
                ->where('semester_id', $krs->semester_id)
                ->first();

            $status = $pembayaran?->status instanceof \BackedEnum ? $pembayaran->status->value : $pembayaran?->status;

            if (! $pembayaran || $status !== PembayaranStatusEnum::LUNAS->value) {
                throw ValidationException::withMessages([
                    'pembayaran' => sprintf(
                        'KRS Mahasiswa NIM %s tidak dapat disetujui karena pembayaran UKT semester ini belum Lunas.',
                        $krs->mahasiswa->nim ?? '-'
                    ),
                ]);
            }
        }

        DB::transaction(function () use ($krs, $approver) {
            $krs->update([
                'status' => KrsStatusEnum::DISETUJUI,
                'disetujui_oleh' => $approver->id,
            ]);
        });
    }

    /**
     * Menolak (Reject) KRS Mahasiswa.
     */
    public function reject(Krs $krs, User $rejector, ?string $catatan = null): void
    {
        DB::transaction(function () use ($krs, $rejector, $catatan) {
            $krs->update([
                'status' => KrsStatusEnum::DITOLAK,
                'disetujui_oleh' => $rejector->id,
                'catatan' => $catatan,
            ]);
        });
    }

    /**
     * Mengajukan KRS dari status DRAFT menjadi MENUNGGU APPROVAL.
     */
    public function submitForApproval(Krs $krs): void
    {
        if ($krs->details()->count() === 0) {
            throw ValidationException::withMessages([
                'krs' => 'KRS masih kosong. Tambahkan minimal 1 kelas sebelum mengajukan KRS.',
            ]);
        }

        DB::transaction(function () use ($krs) {
            $krs->update([
                'status' => KrsStatusEnum::MENUNGGU,
            ]);
        });
    }
}
