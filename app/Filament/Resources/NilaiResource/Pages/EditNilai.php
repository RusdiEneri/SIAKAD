<?php

namespace App\Filament\Resources\NilaiResource\Pages;

use App\Enums\NilaiHurufEnum;
use App\Filament\Resources\NilaiResource;
use App\Models\KrsDetail;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNilai extends EditRecord
{
    protected static string $resource = NilaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $krsDetail = KrsDetail::find($data['krs_detail_id']);
        if ($krsDetail) {
            $data['sks'] = $krsDetail->kelas->mataKuliah->sks ?? 0;
            $data['tahun_akademik'] = $krsDetail->kelas->semester->nama ?? '2025/2026';
        }

        $enum = NilaiHurufEnum::tryFrom($data['nilai_huruf'] ?? '');
        if ($enum) {
            $data['bobot'] = $enum->bobot();
            $data['lulus'] = $enum->isLulus();
        }

        return $data;
    }
}
