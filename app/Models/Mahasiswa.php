<?php

namespace App\Models;

use App\Enums\StatusMahasiswaEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'prodi_id',
        'angkatan',
        'dosen_wali_id',
        'status',
        'tanggal_masuk',
        'tanggal_lulus',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusMahasiswaEnum::class,
            'tanggal_masuk' => 'date',
            'tanggal_lulus' => 'date',
            'angkatan' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_wali_id');
    }

    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }
}
