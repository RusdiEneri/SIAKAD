<?php

namespace App\Models;

use App\Enums\PembayaranJenisEnum;
use App\Enums\PembayaranStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mahasiswa_id',
        'semester_id',
        'jenis',
        'nominal',
        'status',
        'tanggal_bayar',
        'referensi',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => PembayaranJenisEnum::class,
            'status' => PembayaranStatusEnum::class,
            'nominal' => 'decimal:2',
            'tanggal_bayar' => 'date',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
