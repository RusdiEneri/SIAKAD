<?php

namespace App\Enums;

enum KrsStatusEnum: string
{
    case DRAFT = 'draft';
    case MENUNGGU = 'menunggu';
    case DISETUJUI = 'disetujui';
    case DITOLAK = 'ditolak';
    case BATAL = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::MENUNGGU => 'Menunggu Approval',
            self::DISETUJUI => 'Disetujui',
            self::DITOLAK => 'Ditolak',
            self::BATAL => 'Batal',
        };
    }
}
